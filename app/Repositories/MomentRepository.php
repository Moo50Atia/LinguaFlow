<?php

namespace App\Repositories;

use App\Models\Moment;
use App\Repositories\Interfaces\MomentRepositoryInterface;

class MomentRepository extends BaseRepository implements MomentRepositoryInterface
{
    public function __construct(Moment $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
