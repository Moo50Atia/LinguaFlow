<?php

namespace App\Repositories;

use App\Models\LessonCompletion;
use App\Repositories\Interfaces\LessonCompletionRepositoryInterface;

class LessonCompletionRepository extends BaseRepository implements LessonCompletionRepositoryInterface
{
    public function __construct(LessonCompletion $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
