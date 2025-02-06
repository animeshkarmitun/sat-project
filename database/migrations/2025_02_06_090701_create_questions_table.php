<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            // Use UUID for scalability and distributed systems
            $table->uuid('question_id')->primary();
            
            // Foreign Key: Each question belongs to a section & subject
            $table->uuid('section_id');
            $table->uuid('subject_id');

            // Question Details
            $table->text('question_text');
            $table->enum('question_type', ['MCQ', 'Grid-In'])->default('MCQ');
            $table->json('options')->nullable()->comment('Stores possible answers for MCQ');
            $table->string('correct_answer', 255);
            $table->enum('difficulty', ['Easy', 'Medium', 'Hard'])->default('Medium');
            $table->json('tags')->nullable()->comment('Tags for categorization (JSON)');
            $table->text('explanation')->nullable();
            $table->integer('version_number')->default(1)->comment('Tracks updates to questions');
            $table->string('language_code', 10)->default('en')->comment('Supports multiple languages');

            // Media Support
            $table->json('images')->nullable()->comment('Stores multiple image URLs in JSON format');
            $table->json('videos')->nullable()->comment('Stores multiple video URLs in JSON format');

            // Audit Fields: Tracking who created and updated the question
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();

            // Soft Deletes for safe data removal
            $table->softDeletes();

            // Timestamps for auditing
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('section_id')->references('section_id')->on('sections')->onDelete('cascade');
            $table->foreign('subject_id')->references('subject_id')->on('subjects')->onDelete('cascade');
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('user_id')->on('users')->onDelete('set null');

            // Indexing for Performance
            $table->index(['section_id'], 'idx_section_id');
            $table->index(['difficulty'], 'idx_difficulty');
            $table->index(['deleted_at'], 'idx_deleted_at');
            $table->index(['language_code'], 'idx_language_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
}
