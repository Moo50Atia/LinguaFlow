<?php

namespace App\Repositories;

use App\Models\ChatMember;
use App\Repositories\Interfaces\ChatMemberRepositoryInterface;

class ChatMemberRepository extends BaseRepository implements ChatMemberRepositoryInterface
{
    public function __construct(ChatMember $model)
    {
        parent::__construct($model);
    }
    
    // Implement specific methods from interface here
}
