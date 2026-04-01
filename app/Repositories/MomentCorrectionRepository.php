<?php

namespace App\Repositories;

use App\Models\MomentCorrection;
use App\Repositories\Interfaces\MomentCorrectionRepositoryInterface;

class MomentCorrectionRepository extends BaseRepository implements MomentCorrectionRepositoryInterface
{
    public function __construct(MomentCorrection $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
