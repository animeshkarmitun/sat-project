<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answers', function (Blueprint $table) {
            // Use UUID for scalability and distributed systems
            $table->uuid('answer_id')->primary();
            
            // Foreign Key: Each answer belongs to a test attempt and a question
            $table->uuid('attempt_id');
            $table->uuid('question_id');

            // Answer Details
            $table->string('student_answer', 255)->nullable()->comment('User-submitted answer');
            $table->boolean('is_correct')->default(false)->comment('Indicates if the answer was correct');
            $table->integer('time_spent')->unsigned()->nullable()->comment('Time spent on the question in seconds');

            // Media Support
            $table->json('image_urls')->nullable()->comment('Stores multiple image URLs in JSON format');
            $table->json('video_urls')->nullable()->comment('Stores multiple video URLs in JSON format');

            // Soft Deletes for safe data removal
            $table->softDeletes();

            // Timestamps for auditing
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('attempt_id')->references('attempt_id')->on('test_attempts')->onDelete('cascade');
            $table->foreign('question_id')->references('question_id')->on('questions')->onDelete('cascade');

            // Indexing for Performance
            $table->index(['attempt_id'], 'idx_attempt_id');
            $table->index(['question_id'], 'idx_question_id');
            $table->index(['is_correct'], 'idx_is_correct');
            $table->index(['deleted_at'], 'idx_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('answers');
    }
}
