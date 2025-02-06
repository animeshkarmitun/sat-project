<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Answer extends Model
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
        'answer_id', 'attempt_id', 'question_id', 'student_answer',
        'is_correct', 'time_spent', 'image_urls', 'video_urls'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_correct' => 'boolean',
        'time_spent' => 'integer',
        'image_urls' => 'array',
        'video_urls' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to generate a UUID before creating a new answer.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($answer) {
            if (empty($answer->answer_id)) {
                $answer->answer_id = (string) Str::uuid();
            }

            // Trim the student's answer for consistent storage
            $answer->student_answer = trim($answer->student_answer);
        });
    }

    /**
     * Scope: Retrieve answers for a specific test attempt.
     */
    public function scopeByAttempt($query, $attemptId)
    {
        return $query->where('attempt_id', $attemptId);
    }

    /**
     * Scope: Retrieve only correct answers.
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope: Retrieve only incorrect answers.
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Relationship: An answer belongs to a test attempt.
     */
    public function attempt()
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id', 'attempt_id');
    }

    /**
     * Relationship: An answer belongs to a question.
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }
}
