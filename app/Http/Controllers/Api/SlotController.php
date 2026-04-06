<?php

namespace App\Http\Controllers\Api;

use App\Models\InstructorSlot;
use App\Http\Requests\Learning\StoreSlotRequest;
use App\Services\InstructorSlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlotController extends BaseController
{
    public function __construct(
        protected InstructorSlotService $slotService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $slots = $this->slotService->listAvailable(
            $request->user()->instructor->id,
            $request->get('month'),
            $request->get('year')
        );

        return $this->sendSuccess([
            'slots' => $slots
        ]);
    }

    public function store(StoreSlotRequest $request): JsonResponse
    {
        $slot = $this->slotService->create(
            $request->user()->instructor->id,
            $request->validated()
        );

        return $this->sendCreated([
            'slot' => $slot
        ]);
    }

    public function destroy(InstructorSlot $slot): JsonResponse
    {
        if ($slot->instructor_id !== request()->user()->instructor->id) {
            abort(403);
        }

        try {
            $this->slotService->delete($slot);
            return $this->sendDeleted();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422);
        }
    }
}
