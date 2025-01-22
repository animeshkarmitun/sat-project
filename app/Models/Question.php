<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'question_text',
        'question_type',
        'difficulty_level',
        'is_active',
        'order',
    ];

    /**
     * Define a relationship to the section.
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Define a relationship to options.
     */
    public function options()
    {
        return $this->hasMany(Option::class);
    }
}
