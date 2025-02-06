<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Question extends Model
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
        'question_id', 'section_id', 'subject_id', 'question_text', 'question_type',
        'options', 'correct_answer', 'difficulty', 'tags', 'explanation',
        'version_number', 'language_code', 'images', 'videos',
        'created_by', 'updated_by',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'options' => 'array',
        'tags' => 'array',
        'images' => 'array',
        'videos' => 'array',
        'version_number' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to generate a UUID before creating a new question.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($question) {
            if (empty($question->question_id)) {
                $question->question_id = (string) Str::uuid();
            }

            // Ensure proper formatting for question text
            $question->question_text = ucfirst(trim($question->question_text));
        });
    }

    /**
     * Scope: Retrieve only questions for a specific section.
     */
    public function scopeBySection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    /**
     * Scope: Retrieve questions filtered by difficulty.
     */
    public function scopeDifficulty($query, $level)
    {
        return $query->where('difficulty', $level);
    }

    /**
     * Scope: Retrieve questions for a specific language.
     */
    public function scopeByLanguage($query, $languageCode)
    {
        return $query->where('language_code', $languageCode);
    }

    /**
     * Relationship: A question belongs to a section.
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    /**
     * Relationship: A question belongs to a subject.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    /**
     * Relationship: The admin who created the question.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Relationship: The admin who last updated the question.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }
}
