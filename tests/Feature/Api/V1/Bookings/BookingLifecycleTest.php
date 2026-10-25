<?php

namespace Tests\Feature\Api\V1\Bookings;

use App\Constants\BookingStatus;
use App\Constants\RoomStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Hotel\HotelSettingService;
use Tests\ApiTestCase;
use Tests\Traits\InteractsWithHotels;

class BookingLifecycleTest extends ApiTestCase
{
    use InteractsWithHotels;

    public function test_room_status_becomes_reserved_on_booking_creation(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $guest = Guest::factory()->create();
        $roomType = RoomType::factory()->create([
            'hotel_id' => $hotel->id,
        ]);

        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::AVAILABLE,
        ]);

        $response = $this->postJson(route('bookings.store', $hotel), [
            'guest_id' => $guest->id,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'rooms' => [$room->id],
            'status' => BookingStatus::PENDING,
        ]);

        $response->assertCreated();

        $room->refresh();

        $this->assertEquals(
            RoomStatus::RESERVED,
            $room->status
        );
    }

    public function test_booking_lifecycle_transitions(): void
    {
        $user = $this->actingAsRole('super_admin');
        $hotel = $this->createHotelForUser($user);

        $guest = Guest::factory()->create();

        $roomType = RoomType::factory()->create([
            'hotel_id' => $hotel->id,
        ]);

        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'status' => BookingStatus::PENDING,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
        ]);

        $booking->rooms()->attach($room->id, [
            'price_per_night' => 100.00,
        ]);

        // Check in booking.
        $response = $this->postJson(
            route('bookings.check-in', [$hotel, $booking])
        );

        $response->assertOk();

        $booking->refresh();
        $room->refresh();

        $this->assertEquals(
            BookingStatus::CHECKED_IN,
            $booking->status
        );

        $this->assertEquals(
            RoomStatus::OCCUPIED,
            $room->status
        );

        $this->assertNotNull($booking->actual_check_in_at);

        // Cancelling a checked-in booking should fail.
        $response = $this->postJson(
            route('bookings.cancel', [$hotel, $booking])
        );

        $response->assertStatus(422);

        // Check out booking.
        $response = $this->postJson(
            route('bookings.check-out', [$hotel, $booking])
        );

        $response->assertOk();

        $booking->refresh();
        $room->refresh();

        $this->assertEquals(
            BookingStatus::CHECKED_OUT,
            $booking->status
        );

        $this->assertEquals(
            RoomStatus::CLEANING,
            $room->status
        );

        $this->assertNotNull($booking->actual_check_out_at);

        // Marking a checked-out booking as no-show should fail.
        $response = $this->postJson(
            route('bookings.no-show', [$hotel, $booking])
        );

        $response->assertStatus(422);
    }

    public function test_no_show_transitions_status_and_releases_room(): void
    {
        $user = $this->actingAsRole('super_admin');
        $hotel = $this->createHotelForUser($user);

        $guest = Guest::factory()->create();

        $roomType = RoomType::factory()->create([
            'hotel_id' => $hotel->id,
        ]);

        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::RESERVED,
        ]);

        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'status' => BookingStatus::CONFIRMED,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
        ]);

        $booking->rooms()->attach($room->id, [
            'price_per_night' => 100.00,
        ]);

        $response = $this->postJson(
            route('bookings.no-show', [$hotel, $booking])
        );

        $response->assertOk();

        $booking->refresh();
        $room->refresh();

        $this->assertEquals(
            BookingStatus::NO_SHOW,
            $booking->status
        );

        $this->assertEquals(
            RoomStatus::AVAILABLE,
            $room->status
        );
    }

    public function test_overbooking_setting_enforcement(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        // Disable overbooking.
        $settings = app(HotelSettingService::class)
            ->getSettings($hotel);

        app(HotelSettingService::class)->update(
            $settings,
            ['allow_overbooking' => false]
        );

        $guest1 = Guest::factory()->create();
        $guest2 = Guest::factory()->create();

        $roomType = RoomType::factory()->create([
            'hotel_id' => $hotel->id,
        ]);

        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        // Use future dates so the test remains valid over time.
        $firstCheckIn = now()->addDays(10)->toDateString();
        $firstCheckOut = now()->addDays(14)->toDateString();

        $secondCheckIn = now()->addDays(12)->toDateString();
        $secondCheckOut = now()->addDays(15)->toDateString();

        // Create the first booking.
        $this->postJson(route('bookings.store', $hotel), [
            'guest_id' => $guest1->id,
            'check_in_date' => $firstCheckIn,
            'check_out_date' => $firstCheckOut,
            'rooms' => [$room->id],
            'status' => BookingStatus::CONFIRMED,
        ])->assertCreated();

        // An overlapping booking should fail when overbooking is disabled.
        $this->postJson(route('bookings.store', $hotel), [
            'guest_id' => $guest2->id,
            'check_in_date' => $secondCheckIn,
            'check_out_date' => $secondCheckOut,
            'rooms' => [$room->id],
            'status' => BookingStatus::CONFIRMED,
        ])->assertStatus(422);

        // Enable overbooking.
        $settings = app(HotelSettingService::class)
            ->getSettings($hotel);

        app(HotelSettingService::class)->update(
            $settings,
            ['allow_overbooking' => true]
        );

        // The overlapping booking should now succeed and be flagged.
        $response = $this->postJson(route('bookings.store', $hotel), [
            'guest_id' => $guest2->id,
            'check_in_date' => $secondCheckIn,
            'check_out_date' => $secondCheckOut,
            'rooms' => [$room->id],
            'status' => BookingStatus::CONFIRMED,
        ]);

        $response->assertCreated();

        $this->assertTrue(
            (bool) $response->json('data.is_overbooked')
        );
    }

    public function test_cancellation_window_enforcement(): void
    {
        $user = $this->actingAsRole('hotel_manager');
        $hotel = $this->createHotelForUser($user);

        $settings = app(HotelSettingService::class)
            ->getSettings($hotel);

        app(HotelSettingService::class)->update($settings, [
            'booking_cancellation_hours' => 24,
            'check_in_time' => '14:00',
        ]);

        $guest = Guest::factory()->create();

        $roomType = RoomType::factory()->create([
            'hotel_id' => $hotel->id,
        ]);

        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
        ]);

        // A booking checking in today should be outside the cancellation window.
        $booking = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'status' => BookingStatus::CONFIRMED,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
        ]);

        $booking->rooms()->attach($room->id, [
            'price_per_night' => 100.00,
        ]);

        $response = $this->postJson(
            route('bookings.cancel', [$hotel, $booking])
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        // A booking checking in three days from now should be cancellable.
        $booking2 = Booking::factory()->create([
            'hotel_id' => $hotel->id,
            'guest_id' => $guest->id,
            'status' => BookingStatus::CONFIRMED,
            'check_in_date' => now()->addDays(3)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(),
        ]);

        $booking2->rooms()->attach($room->id, [
            'price_per_night' => 100.00,
        ]);

        $response2 = $this->postJson(
            route('bookings.cancel', [$hotel, $booking2])
        );

        $response2->assertOk();

        $this->assertEquals(
            BookingStatus::CANCELLED,
            $booking2->refresh()->status
        );
    }
}
