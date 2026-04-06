<?php

namespace App\Actions\Community;

use App\Models\Moment;
use App\Models\MomentCorrection;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitMomentCorrectionAction
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function execute(User $corrector, int $momentId, array $data): MomentCorrection
    {
        return DB::transaction(function () use ($corrector, $momentId, $data) {
            $moment = Moment::findOrFail($momentId);

            if ($moment->user_id === $corrector->id) {
                throw ValidationException::withMessages([
                    'moment_id' => ['You cannot correct your own moment.']
                ]);
            }

            $correction = MomentCorrection::create([
                'moment_id'      => $momentId,
                'corrector_id'   => $corrector->id,
                'corrected_text' => $data['corrected_text'],
                'notes'          => $data['notes'] ?? null,
            ]);

            // Notify the original author
            $this->notificationService->create(
                $moment->user_id,
                'moment_correction',
                'New Correction Received',
                "{$corrector->name} has provided a grammar correction for your recent post."
            );

            return $correction;
        });
    }
}
