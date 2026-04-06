<?php

namespace App\Http\Controllers\Api;

use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends BaseController
{
    public function __construct(
        protected TranslationService $translationService
    ) {}

    /**
     * Translate text via API.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function translate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text'        => 'required|string',
            'target_lang' => 'required|string|max:5',
            'source_lang' => 'nullable|string|max:5',
        ]);

        $result = $this->translationService->translate(
            $validated['text'], 
            $validated['target_lang'], 
            $validated['source_lang'] ?? null
        );

        return $this->sendSuccess($result, 'Translation successful');
    }
}
