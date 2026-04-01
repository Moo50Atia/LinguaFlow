<?php

namespace App\Repositories;

use App\Models\Chat;
use App\Repositories\Interfaces\ChatRepositoryInterface;

class ChatRepository extends BaseRepository implements ChatRepositoryInterface
{
    public function __construct(Chat $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
