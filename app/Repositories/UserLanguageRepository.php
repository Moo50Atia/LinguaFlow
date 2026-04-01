<?php

namespace App\Repositories;

use App\Models\UserLanguage;
use App\Repositories\Interfaces\UserLanguageRepositoryInterface;

class UserLanguageRepository extends BaseRepository implements UserLanguageRepositoryInterface
{
    public function __construct(UserLanguage $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
