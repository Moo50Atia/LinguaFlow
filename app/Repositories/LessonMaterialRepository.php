<?php

namespace App\Repositories;

use App\Models\LessonMaterial;
use App\Repositories\Interfaces\LessonMaterialRepositoryInterface;

class LessonMaterialRepository extends BaseRepository implements LessonMaterialRepositoryInterface
{
    public function __construct(LessonMaterial $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
