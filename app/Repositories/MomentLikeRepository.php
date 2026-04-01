<?php

namespace App\Repositories;

use App\Models\MomentLike;
use App\Repositories\Interfaces\MomentLikeRepositoryInterface;

class MomentLikeRepository extends BaseRepository implements MomentLikeRepositoryInterface
{
    public function __construct(MomentLike $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
