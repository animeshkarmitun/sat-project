<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_attempts', function (Blueprint $table) {
            // Use UUID for scalability and distributed systems
            $table->uuid('attempt_id')->primary();
            
            // Foreign Key: Each attempt belongs to a user and a test
            $table->uuid('test_id');
            $table->uuid('user_id');

            // Attempt Details
            $table->timestamp('start_time')->default(now());
            $table->timestamp('end_time')->nullable();
            $table->uuid('last_question_id')->nullable()->comment('Tracks the last answered question');
            $table->integer('remaining_time')->unsigned()->nullable()->comment('Time left in seconds');

            // Performance Metrics
            $table->decimal('score', 5, 2)->nullable()->comment('Final test score');
            $table->enum('status', ['in_progress', 'completed', 'paused'])->default('in_progress');
            $table->boolean('is_autosaved')->default(true)->comment('Indicates if the attempt is autosaved');
            $table->json('completed_sections')->nullable()->comment('JSON stores sections completed');

            // Device & Security Information
            $table->string('device_info', 255)->nullable()->comment('Stores device model & OS');
            $table->string('ip_address', 45)->nullable()->comment('Stores user IP address');

            // Soft Deletes for safe data removal
            $table->softDeletes();

            // Timestamps for auditing
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('test_id')->references('test_id')->on('tests')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('last_question_id')->references('question_id')->on('questions')->onDelete('set null');

            // Indexing for Performance
            $table->index(['test_id'], 'idx_test_id');
            $table->index(['user_id'], 'idx_user_id');
            $table->index(['status'], 'idx_status');
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
        Schema::dropIfExists('test_attempts');
    }
}
