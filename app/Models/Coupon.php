<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Coupon extends Model
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
        'coupon_id', 'coupon_code', 'discount_type', 'discount_value', 
        'min_purchase_amount', 'max_redemptions', 'redemptions_used', 
        'expires_at', 'status', 'redeemed_by'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_redemptions' => 'integer',
        'redemptions_used' => 'integer',
        'expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to generate a UUID before creating a new coupon.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($coupon) {
            if (empty($coupon->coupon_id)) {
                $coupon->coupon_id = (string) Str::uuid();
            }

            // Ensure coupon codes are stored in uppercase format
            $coupon->coupon_code = strtoupper(trim($coupon->coupon_code));
        });
    }

    /**
     * Scope: Retrieve active coupons.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope: Retrieve expired coupons.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }

    /**
     * Scope: Retrieve coupons by discount type.
     */
    public function scopeByDiscountType($query, $discountType)
    {
        return $query->where('discount_type', $discountType);
    }

    /**
     * Scope: Retrieve coupons by redemption status.
     */
    public function scopeAvailableForRedemption($query)
    {
        return $query->whereColumn('redemptions_used', '<', 'max_redemptions')
            ->where('status', 'active')
            ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Relationship: A coupon may be redeemed by a user.
     */
    public function redeemedBy()
    {
        return $this->belongsTo(User::class, 'redeemed_by', 'user_id');
    }

    /**
     * Check if the coupon is still valid.
     */
    public function isValid()
    {
        return $this->status === 'active' 
            && Carbon::parse($this->expires_at)->isFuture() 
            && $this->redemptions_used < $this->max_redemptions;
    }

    /**
     * Apply the coupon discount to a given amount.
     */
    public function applyDiscount($amount)
    {
        if ($this->discount_type === 'percentage') {
            return max(0, $amount - ($amount * ($this->discount_value / 100)));
        }
        return max(0, $amount - $this->discount_value);
    }
}
