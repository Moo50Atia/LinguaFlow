<?php

namespace App\Repositories;

use App\Models\MomentComment;
use App\Repositories\Interfaces\MomentCommentRepositoryInterface;

class MomentCommentRepository extends BaseRepository implements MomentCommentRepositoryInterface
{
    public function __construct(MomentComment $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
