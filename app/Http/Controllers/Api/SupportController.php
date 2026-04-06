<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Support\ReportProblemRequest;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SupportController extends BaseController
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    public function report(ReportProblemRequest $request): JsonResponse
    {
        $attachmentUrl = null;
        if ($request->hasFile('attachment')) {
            $attachmentUrl = $this->fileUploadService->store($request->file('attachment'), 'support');
        }

        // Ideally this goes to a dedicated SupportTicket model, mapping simplified here
        DB::table('support_tickets')->insert([
            'user_id'     => $request->user()->id,
            'type'        => $request->validated('type'),
            'description' => $request->validated('description'),
            'attachment'  => $attachmentUrl,
            'status'      => 'open',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return $this->sendSuccess([], 'Your problem has been reported. Our team will review it shortly.');
    }
}
