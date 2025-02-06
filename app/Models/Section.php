<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Section extends Model
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
        'section_id', 'test_id', 'section_name', 'section_order',
        'time_limit', 'created_by', 'updated_by',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'section_order' => 'integer',
        'time_limit' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function to generate a UUID before creating a new section.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($section) {
            if (empty($section->section_id)) {
                $section->section_id = (string) Str::uuid();
            }

            // Ensure consistent casing for section names
            $section->section_name = ucwords(strtolower($section->section_name));
        });
    }

    /**
     * Scope: Retrieve only sections for a specific test.
     */
    public function scopeByTest($query, $testId)
    {
        return $query->where('test_id', $testId);
    }

    /**
     * Scope: Retrieve sections in order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('section_order', 'asc');
    }

    /**
     * Relationship: A section belongs to a test.
     */
    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id', 'test_id');
    }

    /**
     * Relationship: The admin who created the section.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Relationship: The admin who last updated the section.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    /**
     * Relationship: A section has many questions.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'section_id', 'section_id');
    }
}

