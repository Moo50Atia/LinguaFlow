<?php

namespace App\Http\Controllers\Api;

use App\Models\Moment;
use App\Http\Requests\Community\StoreMomentRequest;
use App\Http\Requests\Community\StoreCorrectionRequest;
use App\Actions\Community\SubmitMomentCorrectionAction;
use App\Queries\Community\MomentsFeedQuery;
use App\Http\Resources\MomentResource;
use App\Services\MomentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MomentController extends BaseController
{
    use AuthorizesRequests;

    public function __construct(
        protected MomentService $momentService
    ) {}

    public function index(Request $request, MomentsFeedQuery $query): JsonResponse
    {
        $feed = $query->execute($request->user()->id, $request->all());

        return $this->sendSuccess([
            'moments' => MomentResource::collection($feed)->response()->getData(true)
        ]);
    }

    public function store(StoreMomentRequest $request): JsonResponse
    {
        $moment = $this->momentService->create($request->user()->id, $request->validated());

        return $this->sendCreated([
            // Return through resource so client gets normalized data
            'moment' => new MomentResource($moment->load('user'))
        ]);
    }

    public function destroy(Moment $moment): JsonResponse
    {
        $this->authorize('delete', $moment);

        $this->momentService->delete($moment);

        return $this->sendDeleted();
    }

    public function like(Request $request, Moment $moment): JsonResponse
    {
        $result = $this->momentService->toggleLike($request->user()->id, $moment->id);

        return $this->sendSuccess($result, "Moment {$result['status']} successfully.");
    }

    public function correct(StoreCorrectionRequest $request, Moment $moment, SubmitMomentCorrectionAction $action): JsonResponse
    {
        $correction = $action->execute($request->user(), $moment->id, $request->validated());

        return $this->sendCreated([
            'correction' => $correction->load('corrector')
        ], 'Correction submitted.');
    }
}
