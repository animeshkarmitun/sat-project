<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            // Use UUID for scalability and distributed systems
            $table->uuid('subject_id')->primary();
            
            // Subject Details
            $table->string('subject_name', 255)->unique();
            $table->string('language_code', 10)->default('en'); // Supports localization

            // Soft Deletes for safe data removal
            $table->softDeletes();

            // Timestamps for auditing
            $table->timestamps();

            // Indexing for faster lookups
            $table->index(['subject_name'], 'idx_subject_name');
            $table->index(['language_code'], 'idx_language_code');
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
        Schema::dropIfExists('subjects');
    }
}
