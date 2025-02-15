<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'exam_id',
        'section_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'difficulty',
        'tags',
        'explanation',
        'image_url',
        'video_url',
        'is_active',
        'version',
        'hint',
        'time_limit',
        'reference_material',
        'score_weight',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
        'time_limit' => 'integer',
        'score_weight' => 'float',
    ];

    /**
     * Get the exam associated with the question.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the section associated with the question.
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the user who created the question.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the question.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include active questions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by difficulty.
     */
    public function scopeDifficulty($query, $level)
    {
        return $query->where('difficulty', $level);
    }

    /**
     * Scope a query to filter by section.
     */
    public function scopeBySection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    /**
     * Get formatted question text with hint if available.
     *
     * @return string
     */
    public function getFormattedQuestionText(): string
    {
        return $this->hint ? $this->question_text . " (Hint: " . $this->hint . ")" : $this->question_text;
    }

    /**
     * Check if the question has an associated image.
     *
     * @return bool
     */
    public function hasImage(): bool
    {
        return !empty($this->image_url);
    }

    /**
     * Check if the question has an associated video.
     *
     * @return bool
     */
    public function hasVideo(): bool
    {
        return !empty($this->video_url);
    }
}
