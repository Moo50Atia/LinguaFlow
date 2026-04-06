<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Actions\Learning\BookInstructorSessionAction;
use App\Http\Requests\Learning\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookingController extends BaseController
{
    use AuthorizesRequests;

    public function __construct(
        protected BookingService $bookingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // This is the student's booking view. Instructors might have a different route/query like in Dashboard.
        $bookings = $this->bookingService->listForUser($request->user()->id);

        return $this->sendSuccess([
            'bookings' => BookingResource::collection($bookings)->response()->getData(true)
        ]);
    }

    public function store(StoreBookingRequest $request, BookInstructorSessionAction $action): JsonResponse
    {
        $booking = $action->execute($request->user(), $request->validated());

        return $this->sendCreated([
            'booking' => new BookingResource($booking)
        ], 'Booking created successfully.');
    }

    public function cancel(Booking $booking): JsonResponse
    {
        $this->authorize('interact', $booking);

        $actorRole = request()->user()->id === $booking->user_id ? 'student' : 'instructor';
        $this->bookingService->cancel($booking, $actorRole);

        return $this->sendSuccess([], 'Booking cancelled successfully.');
    }

    public function confirm(Booking $booking): JsonResponse
    {
        $this->authorize('interact', $booking);

        // Only instructors can confirm
        if (!request()->user()->instructor || request()->user()->instructor->id !== $booking->instructor_id) {
            abort(403, 'Only the instructor can confirm this booking.');
        }

        $this->bookingService->confirm($booking);

        return $this->sendSuccess([], 'Booking confirmed successfully.');
    }
}
