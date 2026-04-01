<?php

namespace App\Repositories;

use App\Models\InstructorSlot;
use App\Repositories\Interfaces\InstructorSlotRepositoryInterface;

class InstructorSlotRepository extends BaseRepository implements InstructorSlotRepositoryInterface
{
    public function __construct(InstructorSlot $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
