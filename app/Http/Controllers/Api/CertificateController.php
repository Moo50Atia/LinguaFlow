<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CertificateResource;
use App\Http\Resources\UserResource;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends BaseController
{
    public function __construct(
        protected CertificateService $certificateService
    ) {}

    /**
     * Get authed user's certificates.
     */
    public function index(Request $request): JsonResponse
    {
        $certificates = $this->certificateService->getStudentCertificates($request->user()->id);

        return $this->sendSuccess([
            'certificates' => CertificateResource::collection($certificates)
        ]);
    }

    /**
     * Public endpoint to verify a certificate by its unique code.
     */
    public function verify(string $number): JsonResponse
    {
        $certificate = $this->certificateService->verify($number);

        if (!$certificate) {
            return $this->sendError('Certificate not found or invalid.', 404);
        }

        return $this->sendSuccess([
            'certificate' => new CertificateResource($certificate),
            'student'     => new UserResource($certificate->user),
        ], 'Certificate is active and valid.');
    }
}
