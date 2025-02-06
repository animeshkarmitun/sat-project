<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Indicates that the primary key is not auto-incrementing.
     */
    public $incrementing = false;

    /**
     * Specifies the primary key type as a string (UUID).
     */
    protected $keyType = 'string';

    /**
     * The attributes that should be mass-assignable.
     */
    protected $fillable = [
        'subscription_id', 'user_id', 'plan_type', 'start_date', 
        'end_date', 'payment_status', 'payment_gateway', 'transaction_id'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to generate a UUID before creating a new subscription.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (empty($subscription->subscription_id)) {
                $subscription->subscription_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Scope: Retrieve active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('end_date', '>', Carbon::now())->where('payment_status', 'completed');
    }

    /**
     * Scope: Retrieve expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<=', Carbon::now());
    }

    /**
     * Scope: Retrieve subscriptions by plan type.
     */
    public function scopeByPlanType($query, $planType)
    {
        return $query->where('plan_type', $planType);
    }

    /**
     * Scope: Retrieve subscriptions with pending payments.
     */
    public function scopePendingPayments($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Relationship: A subscription belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive()
    {
        return $this->end_date && Carbon::parse($this->end_date)->isFuture() && $this->payment_status === 'completed';
    }

    /**
     * Get the remaining days before subscription expires.
     */
    public function getRemainingDays()
    {
        return $this->end_date ? Carbon::now()->diffInDays(Carbon::parse($this->end_date), false) : null;
    }
}
