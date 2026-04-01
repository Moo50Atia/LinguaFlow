<?php

namespace App\Repositories;

use App\Models\QuizQuestion;
use App\Repositories\Interfaces\QuizQuestionRepositoryInterface;

class QuizQuestionRepository extends BaseRepository implements QuizQuestionRepositoryInterface
{
    public function __construct(QuizQuestion $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
