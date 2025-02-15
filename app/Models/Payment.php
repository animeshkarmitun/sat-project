<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'subscription_id',
        'amount',
        'currency',
        'payment_gateway',
        'payment_method',
        'transaction_id',
        'payment_status',
        'paid_at',
        'refunded_at',
        'fee_amount',
        'net_amount',
        'tax_amount',
        'total_amount',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'fee_amount' => 'float',
        'net_amount' => 'float',
        'tax_amount' => 'float',
        'total_amount' => 'float',
    ];

    /**
     * Get the user who made the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription associated with the payment.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if the payment is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->payment_status === 'completed';
    }

    /**
     * Check if the payment is refunded.
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return !is_null($this->refunded_at);
    }

    /**
     * Mark the payment as refunded.
     */
    public function markAsRefunded(): void
    {
        $this->update([
            'payment_status' => 'refunded',
            'refunded_at' => Carbon::now(),
        ]);
    }

    /**
     * Calculate the net amount after deducting fees and tax.
     */
    public function calculateNetAmount(): void
    {
        $this->update([
            'net_amount' => $this->amount - ($this->fee_amount + $this->tax_amount),
        ]);
    }

    /**
     * Calculate total amount including tax.
     */
    public function calculateTotalAmount(): void
    {
        $this->update([
            'total_amount' => $this->amount + $this->tax_amount,
        ]);
    }

    /**
     * Scope a query to only include completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope a query to only include refunded payments.
     */
    public function scopeRefunded($query)
    {
        return $query->whereNotNull('refunded_at');
    }
}
