<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id', 'sender_id', 'text', 'is_correction',
        'original_text', 'corrected_text'
    ];

    protected $casts = [
        'is_correction' => 'boolean',
    ];

    public function chat()
    {
        // Each message belongs to one specific chat.
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        // The user who sent this message.
        return $this->belongsTo(User::class, 'sender_id');
    }
}
