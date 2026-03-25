<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserLanguage extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'language', 'flag', 'level', 'is_native'];

    protected $casts = [
        'is_native' => 'boolean',
    ];

    public function user()
    {
        // Each language entry belongs to a specific user.
        return $this->belongsTo(User::class);
    }
}
