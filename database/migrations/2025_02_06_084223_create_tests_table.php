<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tests', function (Blueprint $table) {
            // Use UUID for scalability and distributed systems
            $table->uuid('test_id')->primary();
            
            // Test Details
            $table->string('test_name', 255);
            $table->enum('test_type', ['SAT 1', 'SAT 2', 'Personalized']);
            $table->string('category', 255)->nullable(); // Optional test categorization
            $table->integer('duration')->unsigned()->comment('Test duration in minutes');
            $table->boolean('is_real_sat')->default(false)->comment('Whether the test is an official SAT');
            $table->boolean('retry_allowed')->default(true)->comment('If users can retake the test');
            $table->integer('max_attempts')->unsigned()->nullable()->comment('Maximum number of attempts allowed');

            // Foreign Keys
            $table->uuid('created_by'); // Tracks the admin who created the test
            $table->uuid('updated_by')->nullable(); // Tracks the admin who last updated the test

            // Localization & Soft Deletes
            $table->string('language_code', 10)->default('en'); // Supports multiple languages
            $table->softDeletes(); // Enables soft deletion
            
            // Timestamps for auditing
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('user_id')->on('users')->onDelete('set null');

            // Indexing for Performance
            $table->index(['test_type'], 'idx_test_type');
            $table->index(['is_real_sat'], 'idx_is_real_sat');
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
        Schema::dropIfExists('tests');
    }
}
