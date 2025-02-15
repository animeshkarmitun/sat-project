<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Answer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'answers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'question_id',
        'attempt_id',
        'student_answer',
        'is_correct',
        'time_spent',
        'image_url',
        'video_url',
        'score',
        'submitted_at',
        'reviewed_by',
        'review_comment',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_correct' => 'boolean',
        'time_spent' => 'integer',
        'score' => 'float',
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the user who submitted the answer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the question related to this answer.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the test attempt associated with the answer.
     */
    public function attempt()
    {
        return $this->belongsTo(TestAttempt::class);
    }

    /**
     * Get the reviewer of the answer.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope a query to filter correct answers.
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope a query to filter incorrect answers.
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope a query to filter answers by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter answers by test attempt.
     */
    public function scopeByAttempt($query, $attemptId)
    {
        return $query->where('attempt_id', $attemptId);
    }

    /**
     * Log answer review details.
     */
    public function logReview(): void
    {
        Log::info('Answer reviewed', [
            'answer_id' => $this->id,
            'reviewed_by' => $this->reviewed_by,
            'review_comment' => $this->review_comment,
        ]);
    }

    /**
     * Get formatted answer submission details.
     *
     * @return string
     */
    public function getFormattedSubmissionDetails(): string
    {
        return "Answer ID: {$this->id}, Submitted by User: {$this->user_id}, Score: {$this->score}, Submitted at: " . $this->submitted_at->format('Y-m-d H:i:s');
    }
}
