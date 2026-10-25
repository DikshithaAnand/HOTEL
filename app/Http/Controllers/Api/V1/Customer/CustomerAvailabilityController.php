<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\Room\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerAvailabilityController extends Controller
{
    public function __construct(
        private AvailabilityService $availabilityService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please check your booking dates.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $hotel = Hotel::query()
            ->where('slug', 'temairazu-hotel')
            ->where('is_active', true)
            ->first();

        if (! $hotel) {
            return response()->json([
                'success' => false,
                'message' => 'The hotel is currently unavailable.',
            ], 404);
        }

        $availability = $this->availabilityService->getAvailabilityMatrix(
            $hotel,
            Carbon::parse($request->input('start_date')),
            Carbon::parse($request->input('end_date'))
        );

        return response()->json([
            'success' => true,
            'data' => $availability,
        ]);
    }
}
