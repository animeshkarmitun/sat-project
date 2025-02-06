<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Refund extends Model
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
        'refund_id', 'user_id', 'payment_id', 'refund_amount', 
        'refund_status', 'refund_method', 'refund_reason', 'processed_at'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'refund_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to generate a UUID before creating a new refund entry.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($refund) {
            if (empty($refund->refund_id)) {
                $refund->refund_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Scope: Retrieve refunds for a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Retrieve refunds for a specific payment.
     */
    public function scopeByPayment($query, $paymentId)
    {
        return $query->where('payment_id', $paymentId);
    }

    /**
     * Scope: Retrieve pending refunds.
     */
    public function scopePending($query)
    {
        return $query->where('refund_status', 'pending');
    }

    /**
     * Scope: Retrieve approved refunds.
     */
    public function scopeApproved($query)
    {
        return $query->where('refund_status', 'approved');
    }

    /**
     * Scope: Retrieve rejected refunds.
     */
    public function scopeRejected($query)
    {
        return $query->where('refund_status', 'rejected');
    }

    /**
     * Scope: Retrieve refunds within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('processed_at', [$startDate, $endDate]);
    }

    /**
     * Relationship: A refund belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relationship: A refund belongs to a payment.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    /**
     * Check if the refund has been processed.
     */
    public function isProcessed()
    {
        return !is_null($this->processed_at);
    }

    /**
     * Get the formatted refund processed date.
     */
    public function getFormattedProcessedDate()
    {
        return $this->processed_at ? Carbon::parse($this->processed_at)->format('d M Y, H:i:s') : 'Not Processed';
    }
}
