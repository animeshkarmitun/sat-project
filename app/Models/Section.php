<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'title',
        'description',
        'subject',  // New field for SAT 2 subjects
        'order',
    ];

    /**
     * Define a relationship to the exam.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Define a relationship to questions.
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
