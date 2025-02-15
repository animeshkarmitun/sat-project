<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'plan_type',
        'start_date',
        'end_date',
        'payment_status',
        'payment_gateway',
        'transaction_id',
        'auto_renew',
        'discount_applied',
        'canceled_at',
        'renewal_attempts',
        'previous_end_date',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'canceled_at' => 'datetime',
        'previous_end_date' => 'datetime',
        'auto_renew' => 'boolean',
        'discount_applied' => 'float',
        'renewal_attempts' => 'integer',
    ];

    /**
     * Get the user associated with the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('end_date', '>=', Carbon::now());
    }

    /**
     * Check if the subscription is currently active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->end_date->gte(Carbon::now()) && is_null($this->canceled_at);
    }

    /**
     * Calculate remaining days in the subscription.
     *
     * @return int
     */
    public function getRemainingDays(): int
    {
        return Carbon::now()->diffInDays($this->end_date, false);
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(): void
    {
        $this->update(['canceled_at' => Carbon::now(), 'auto_renew' => false]);
    }

    /**
     * Attempt to renew the subscription.
     */
    public function attemptRenewal(): bool
    {
        if (!$this->auto_renew || $this->renewal_attempts >= 3) {
            return false;
        }

        $this->update([
            'previous_end_date' => $this->end_date,
            'end_date' => Carbon::now()->addDays(30),
            'renewal_attempts' => $this->renewal_attempts + 1,
        ]);

        return true;
    }

    /**
     * Check if the subscription has expired.
     *
     * @return bool
     */
    public function hasExpired(): bool
    {
        return Carbon::now()->gt($this->end_date);
    }

    /**
     * Extend subscription manually.
     *
     * @param int $days
     */
    public function extendSubscription(int $days): void
    {
        $this->update(['end_date' => $this->end_date->addDays($days)]);
    }
}
