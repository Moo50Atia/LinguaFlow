<?php

namespace App\Repositories;

use App\Models\StudyDay;
use App\Repositories\Interfaces\StudyDayRepositoryInterface;

class StudyDayRepository extends BaseRepository implements StudyDayRepositoryInterface
{
    public function __construct(StudyDay $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
