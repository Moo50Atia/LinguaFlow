<?php

namespace App\Repositories;

use App\Models\Certificate;
use App\Repositories\Interfaces\CertificateRepositoryInterface;

class CertificateRepository extends BaseRepository implements CertificateRepositoryInterface
{
    public function __construct(Certificate $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
