<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Test extends Model
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
        'test_id', 'test_name', 'test_type', 'category', 'duration',
        'is_real_sat', 'retry_allowed', 'max_attempts',
        'created_by', 'updated_by', 'language_code',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_real_sat' => 'boolean',
        'retry_allowed' => 'boolean',
        'max_attempts' => 'integer',
        'duration' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to generate a UUID before creating a new test.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($test) {
            if (empty($test->test_id)) {
                $test->test_id = (string) Str::uuid();
            }

            // Ensure consistent casing for test names
            $test->test_name = ucwords(strtolower($test->test_name));
        });
    }

    /**
     * Scope: Retrieve only SAT 1 Tests.
     */
    public function scopeSat1($query)
    {
        return $query->where('test_type', 'SAT 1');
    }

    /**
     * Scope: Retrieve only SAT 2 Tests.
     */
    public function scopeSat2($query)
    {
        return $query->where('test_type', 'SAT 2');
    }

    /**
     * Scope: Retrieve only Real SAT Tests.
     */
    public function scopeRealSat($query)
    {
        return $query->where('is_real_sat', true);
    }

    /**
     * Scope: Retrieve tests for a specific language.
     */
    public function scopeByLanguage($query, $languageCode)
    {
        return $query->where('language_code', $languageCode);
    }

    /**
     * Relationship: The admin who created the test.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Relationship: The admin who last updated the test.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    /**
     * Relationship: A test has many sections.
     */
    public function sections()
    {
        return $this->hasMany(Section::class, 'test_id', 'test_id');
    }

    /**
     * Relationship: A test has many attempts.
     */
    public function attempts()
    {
        return $this->hasMany(TestAttempt::class, 'test_id', 'test_id');
    }
}
