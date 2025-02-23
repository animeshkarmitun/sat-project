<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('exam_id');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('remaining_time')->nullable()->comment('Remaining time in seconds');
            $table->enum('status', ['in_progress', 'paused', 'completed', 'expired', 'terminated', 'review_pending'])
                ->default('in_progress')->comment('Exam attempt status');
            $table->decimal('score', 5, 2)->nullable();
            $table->integer('attempt_number')->default(1)->comment('Tracks user attempt count');
            $table->integer('correct_answers')->default(0)->comment('Number of correct answers');
            $table->integer('wrong_answers')->default(0)->comment('Number of incorrect answers');
            $table->json('answers')->nullable()->comment('Stores submitted answers in JSON format');
            $table->json('metadata')->nullable()->comment('Stores additional attempt data');
            $table->ipAddress('ip_address')->nullable()->comment('Tracks IP address of the attempt');
            $table->string('device_info', 255)->nullable()->comment('Stores user device details');
            $table->boolean('cheating_detected')->default(false)->comment('Flag for cheating detection');
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');

            // Indexes for optimization
            $table->index(['user_id', 'exam_id'], 'idx_user_exam');
            $table->index(['status'], 'idx_status');
            $table->index(['ip_address'], 'idx_ip_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exam_attempts');
    }
}
