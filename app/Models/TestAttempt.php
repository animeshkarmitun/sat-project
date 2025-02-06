<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TestAttempt extends Model
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
        'attempt_id', 'test_id', 'user_id', 'last_question_id',
        'remaining_time', 'score', 'status', 'is_autosaved',
        'completed_sections', 'device_info', 'ip_address'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'remaining_time' => 'integer',
        'score' => 'decimal:2',
        'is_autosaved' => 'boolean',
        'completed_sections' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to generate a UUID before creating a new test attempt.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attempt) {
            if (empty($attempt->attempt_id)) {
                $attempt->attempt_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Scope: Retrieve test attempts for a specific test.
     */
    public function scopeByTest($query, $testId)
    {
        return $query->where('test_id', $testId);
    }

    /**
     * Scope: Retrieve attempts for a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Retrieve only completed test attempts.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Retrieve only in-progress test attempts.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Relationship: A test attempt belongs to a test.
     */
    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id', 'test_id');
    }

    /**
     * Relationship: A test attempt belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relationship: A test attempt tracks the last answered question.
     */
    public function lastQuestion()
    {
        return $this->belongsTo(Question::class, 'last_question_id', 'question_id');
    }

    /**
     * Relationship: A test attempt has many answers.
     */
    public function answers()
    {
        return $this->hasMany(Answer::class, 'attempt_id', 'attempt_id');
    }
}
