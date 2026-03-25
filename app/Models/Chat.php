<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'name', 'avatar'];

    public function members()
    {
        // A chat can have many users as participants.
        return $this->belongsToMany(User::class, 'chat_members')
                    ->withPivot('role', 'unread_count')
                    ->withTimestamps();
    }

    public function messages()
    {
        // A chat conversation contains a history of messages.
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        // Helper to get the most recent message for the chat list.
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
