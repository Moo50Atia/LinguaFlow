<?php

namespace App\Services;

use App\Models\Certificate;

class CertificateService
{
    public function getStudentCertificates(int $userId)
    {
        return Certificate::where('user_id', $userId)->latest()->get();
    }

    public function verify(string $certificateNumber): ?Certificate
    {
        return Certificate::with('user')->where('certificate_number', $certificateNumber)->first();
    }
}
