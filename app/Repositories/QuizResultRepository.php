<?php

namespace App\Repositories;

use App\Models\QuizResult;
use App\Repositories\Interfaces\QuizResultRepositoryInterface;

class QuizResultRepository extends BaseRepository implements QuizResultRepositoryInterface
{
    public function __construct(QuizResult $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
