<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            // Link to sections (optional if sub_section_id is provided)
            $table->foreignId('section_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade')
                ->comment('Link to sections table');

            // Link to sub-sections (optional if section_id is provided)
            $table->foreignId('sub_section_id')
                ->nullable()
                ->constrained('sub_sections')
                ->onDelete('cascade')
                ->onUpdate('cascade')
                ->comment('Link to sub_sections table');

            // Question details
            $table->text('question_text')->comment('The text of the question');
            $table->string('question_type')->comment('Type of question: multiple-choice, true/false, etc.');
            $table->unsignedTinyInteger('difficulty_level')->default(1)->comment('Difficulty level on a 1-5 scale');
            $table->boolean('is_active')->default(true)->comment('Indicates if the question is active');
            $table->unsignedInteger('order')->nullable()->comment('Order of the question within the section/sub-section');

            // Timestamps
            $table->timestamps();

            // Indexes for optimization
            $table->index(['section_id', 'sub_section_id', 'is_active'], 'questions_section_sub_section_is_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
