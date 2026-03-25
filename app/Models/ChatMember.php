<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMember extends Model
{
    use HasFactory;

    protected $table = 'chat_members';

    protected $fillable = ['chat_id', 'user_id', 'role', 'unread_count'];

    public function chat()
    {
        // Link back to the parent chat conversation.
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        // Link to the user who is a member of this chat.
        return $this->belongsTo(User::class);
    }
}
