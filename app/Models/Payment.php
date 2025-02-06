<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Payment extends Model
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
        'payment_id', 'user_id', 'subscription_id', 'amount', 
        'currency', 'payment_status', 'payment_method', 
        'transaction_id', 'payment_date'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to generate a UUID before creating a new payment.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_id)) {
                $payment->payment_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Scope: Retrieve payments for a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Retrieve payments for a specific subscription.
     */
    public function scopeBySubscription($query, $subscriptionId)
    {
        return $query->where('subscription_id', $subscriptionId);
    }

    /**
     * Scope: Retrieve completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope: Retrieve failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    /**
     * Scope: Retrieve payments within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Relationship: A payment belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relationship: A payment may be linked to a subscription.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'subscription_id');
    }

    /**
     * Check if the payment is successful.
     */
    public function isSuccessful()
    {
        return $this->payment_status === 'completed';
    }

    /**
     * Get the formatted payment date.
     */
    public function getFormattedPaymentDate()
    {
        return Carbon::parse($this->payment_date)->format('d M Y, H:i:s');
    }
}
