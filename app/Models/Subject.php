<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Subject extends Model
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
        'subject_id', 'subject_name', 'language_code'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to generate a UUID before creating a new subject.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subject) {
            if (empty($subject->subject_id)) {
                $subject->subject_id = (string) Str::uuid();
            }

            // Ensure consistent casing for subject names
            $subject->subject_name = ucwords(strtolower($subject->subject_name));
        });
    }

    /**
     * Scope: Only Active (Non-Deleted) Subjects
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope: Retrieve Subjects for a Specific Language
     */
    public function scopeByLanguage($query, $languageCode)
    {
        return $query->where('language_code', $languageCode);
    }

    /**
     * Relationship: A subject may have many questions.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'subject_id', 'subject_id');
    }
}
