<?php

namespace App\Services\Billing;

use App\Events\Billing\InvoiceGenerated;
use App\Events\Billing\InvoicePaid;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Hotel\HotelSettingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BillingService
{
    /**
     * Retrieve an existing invoice or generate a new one.
     */
    public function getOrGenerateInvoice(Booking $booking): Invoice
    {
        $invoice = $booking->invoices()->first();

        if (
            ! $invoice
            || (float) $invoice->subtotal !== (float) $booking->total_amount
            || $invoice->items()->count() === 0
            || (
                $invoice->tax_amount > 0
                && (float) ($invoice->tax_rate ?? 0) == 0.0
            )
        ) {
            $invoice = $this->regenerateInvoice($booking);
        }

        return $invoice->load('items');
    }

    /**
     * Regenerate a booking invoice inside a database transaction.
     */
    public function regenerateInvoice(Booking $booking): Invoice
    {
        return DB::transaction(function () use ($booking) {
            $hotel = $booking->hotel;
            $settings = app(HotelSettingService::class)->getSettings($hotel);
            $taxRate = $settings->tax_rate ?? 0.00;

            // Calculate the number of nights.
            $checkIn = Carbon::parse($booking->check_in_date);
            $checkOut = Carbon::parse($booking->check_out_date);
            $nights = max(1, $checkIn->diffInDays($checkOut));

            $subtotal = 0.00;
            $itemsData = [];

            // Add room charges.
            foreach ($booking->rooms()->get() as $room) {
                $pricePerNight = $room->pivot->price_per_night;
                $totalPrice = $pricePerNight * $nights;
                $subtotal += $totalPrice;

                $itemsData[] = [
                    'description' => "Room {$room->room_number} Nightly Stay ({$nights} nights)",
                    'quantity' => 1,
                    'unit_price' => $totalPrice,
                    'total_price' => $totalPrice,
                ];
            }

            // Add service charges.
            foreach ($booking->services()->get() as $service) {
                $price = $service->pivot->price;
                $quantity = $service->pivot->quantity;
                $totalPrice = $price * $quantity;
                $subtotal += $totalPrice;

                $itemsData[] = [
                    'description' => "Service: {$service->name}",
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'total_price' => $totalPrice,
                ];
            }

            // Add early check-in fees when applicable.
            if ($booking->actual_check_in_at) {
                $actualCheckIn = Carbon::parse($booking->actual_check_in_at);

                $scheduledCheckInLimit = Carbon::parse(
                    $booking->check_in_date->toDateString().' '.($settings->check_in_time ?? '14:00')
                );

                if ($actualCheckIn->lt($scheduledCheckInLimit)) {
                    $earlyFee = $settings->early_checkin_fee ?? 0.00;

                    if ($earlyFee > 0) {
                        $subtotal += $earlyFee;

                        $itemsData[] = [
                            'description' => 'Early Check-in Fee',
                            'quantity' => 1,
                            'unit_price' => $earlyFee,
                            'total_price' => $earlyFee,
                        ];
                    }
                }
            }

            // Add late check-out fees when applicable.
            if ($booking->actual_check_out_at) {
                $actualCheckOut = Carbon::parse($booking->actual_check_out_at);

                $scheduledCheckOutLimit = Carbon::parse(
                    $booking->check_out_date->toDateString().' '.($settings->check_out_time ?? '11:00')
                )->addMinutes($settings->default_checkout_grace_minutes ?? 0);

                if ($actualCheckOut->gt($scheduledCheckOutLimit)) {
                    $lateFee = $settings->late_checkout_fee ?? 0.00;

                    if ($lateFee > 0) {
                        $subtotal += $lateFee;

                        $itemsData[] = [
                            'description' => 'Late Check-out Fee',
                            'quantity' => 1,
                            'unit_price' => $lateFee,
                            'total_price' => $lateFee,
                        ];
                    }
                }
            }

            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            // Create or update the invoice.
            $invoice = $booking->invoices()->first();

            if (! $invoice) {
                $prefix = $settings->invoice_prefix ?? 'INV-';

                do {
                    $invoiceNumber = $prefix.date('Ymd').strtoupper(Str::random(4));
                } while (Invoice::where('invoice_number', $invoiceNumber)->exists());

                $invoice = Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'booking_id' => $booking->id,
                    'subtotal' => $subtotal,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'status' => 'unpaid',
                ]);
            } else {
                $invoice->update([
                    'subtotal' => $subtotal,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                ]);
            }

            // Synchronize invoice items.
            $invoice->items()->delete();

            foreach ($itemsData as $item) {
                $invoice->items()->create($item);
            }

            $this->recalculateInvoiceStatus($booking);

            $freshInvoice = $invoice->fresh('items');

            event(new InvoiceGenerated($freshInvoice));

            return $freshInvoice;
        });
    }

    /**
     * Record a payment and prevent completed payments exceeding the balance.
     */
    public function logPayment(Booking $booking, array $data): Payment
    {
        return DB::transaction(function () use ($booking, $data) {
            $amount = (float) $data['amount'];
            $status = $data['status'] ?? 'completed';

            $invoice = $booking->invoices()->first();

            if (! $invoice) {
                $invoice = $this->getOrGenerateInvoice($booking);
            }

            if ($status === 'completed') {
                $this->assertOutstandingBalance(
                    $booking,
                    $invoice,
                    $amount
                );
            }

            $payment = $booking->payments()->create([
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'status' => $status,
            ]);

            $this->recalculateInvoiceStatus($booking);

            return $payment;
        });
    }

    /**
     * Update payment status while preventing overpayment when completing it.
     */
    public function updatePaymentStatus(Payment $payment, string $status): Payment
    {
        return DB::transaction(function () use ($payment, $status) {
            $booking = $payment->booking;

            if ($status === 'completed' && $payment->status !== 'completed') {
                $invoice = $booking->invoices()->first();

                if (! $invoice) {
                    $invoice = $this->getOrGenerateInvoice($booking);
                }

                $this->assertOutstandingBalance(
                    $booking,
                    $invoice,
                    (float) $payment->amount,
                    $payment->id
                );
            }

            $payment->update(['status' => $status]);

            $this->recalculateInvoiceStatus($booking);

            return $payment->fresh();
        });
    }

    /**
     * Reject a payment that exceeds the remaining invoice balance.
     *
     * An optional payment ID is excluded when checking a payment
     * that is transitioning from a non-completed status.
     */
    private function assertOutstandingBalance(
        Booking $booking,
        Invoice $invoice,
        float $amount,
        ?int $excludePaymentId = null
    ): void {
        $paymentsQuery = $booking->payments()
            ->where('status', 'completed');

        if ($excludePaymentId !== null) {
            $paymentsQuery->where('id', '!=', $excludePaymentId);
        }

        $paidTotal = (float) $paymentsQuery->sum('amount');

        $outstanding = max(
            0,
            (float) $invoice->total_amount - $paidTotal
        );

        if ($amount > $outstanding + 0.00001) {
            throw ValidationException::withMessages([
                'amount' => [
                    'Payment exceeds the outstanding balance of '
                    .number_format($outstanding, 2, '.', '')
                    .'.',
                ],
            ]);
        }
    }

    /**
     * Recalculate invoice status based on completed payments.
     */
    public function recalculateInvoiceStatus(Booking $booking): void
    {
        $invoice = $booking->invoices()->first();

        if (! $invoice) {
            return;
        }

        $paidTotal = $booking->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $status = 'unpaid';

        if ($paidTotal >= $invoice->total_amount) {
            $status = 'paid';
        } elseif ($paidTotal > 0) {
            $status = 'partial';
        }

        $oldStatus = $invoice->status;

        $invoice->update(['status' => $status]);

        if ($status === 'paid' && $oldStatus !== 'paid') {
            event(new InvoicePaid($invoice));
        }
    }
}
