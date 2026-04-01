<?php

namespace App\Repositories;

use App\Models\UserInterest;
use App\Repositories\Interfaces\UserInterestRepositoryInterface;

class UserInterestRepository extends BaseRepository implements UserInterestRepositoryInterface
{
    public function __construct(UserInterest $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
