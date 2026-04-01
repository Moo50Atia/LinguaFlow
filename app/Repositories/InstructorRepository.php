<?php

namespace App\Repositories;

use App\Models\Instructor;
use App\Repositories\Interfaces\InstructorRepositoryInterface;

class InstructorRepository extends BaseRepository implements InstructorRepositoryInterface
{
    public function __construct(Instructor $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
