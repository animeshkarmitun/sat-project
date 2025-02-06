<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

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
        'user_id', 'name', 'email', 'password', 'password_salt',
        'role', 'is_active', 'ip_address', 'last_login', 'profile_picture_url',
    ];

    /**
     * The attributes that should be hidden from JSON serialization.
     */
    protected $hidden = [
        'password', 'password_salt', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to handle UUID generation and enforce best practices.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->user_id)) {
                $user->user_id = (string) Str::uuid();
            }
        });

        static::updating(function ($user) {
            $user->email = strtolower($user->email); // Ensure email consistency
        });
    }

    /**
     * Scope: Only Active Users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Only Admin Users
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Get the user's profile picture, ensuring a default avatar if not set.
     */
    public function getProfilePictureUrlAttribute($value)
    {
        return $value ?: 'https://example.com/default-avatar.png';
    }

    /**
     * Mutator: Automatically hash passwords before saving.
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * Relationship: A user may have many test attempts.
     */
    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class, 'user_id', 'user_id');
    }
}
