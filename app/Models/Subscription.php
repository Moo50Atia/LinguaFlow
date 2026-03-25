<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'plan', 'price', 'billing_cycle', 'status',
        'stripe_subscription_id', 'stripe_customer_id',
        'trial_ends_at', 'starts_at', 'ends_at', 'cancelled_at'
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        // A subscription belongs to a single user.
        return $this->belongsTo(User::class);
    }
}
