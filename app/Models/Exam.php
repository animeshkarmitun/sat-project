<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'duration',
        'code',
        'type',
        'audience',
        'total_marks',
        'passing_marks',
        'is_active',
        'is_published',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'total_marks' => 'integer',
        'passing_marks' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * Relationships
     */

    // An exam has many sections
    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    // An exam has many tests or instances (if applicable)
    public function tests()
    {
        return $this->hasMany(Test::class);
    }

    // An exam may have results
    public function results()
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Scope a query to only include active exams.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to include only published exams.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
