<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInterest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'interest'];

    public function user()
    {
        // Each interest tag (e.g., 'Travel') belongs to a user.
        return $this->belongsTo(User::class);
    }
}
