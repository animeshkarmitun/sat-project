<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Analytics extends Model
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
        'analytics_id', 'user_id', 'event_type', 'event_details', 'event_timestamp'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'event_details' => 'array',
        'event_timestamp' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to generate a UUID before creating a new analytics entry.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($analytics) {
            if (empty($analytics->analytics_id)) {
                $analytics->analytics_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Scope: Retrieve analytics for a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Retrieve analytics for a specific event type.
     */
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope: Retrieve analytics from a specific date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_timestamp', [$startDate, $endDate]);
    }

    /**
     * Relationship: An analytics entry belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
