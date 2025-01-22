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
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Student details
            $table->string('name')->comment('Full name of the student');
            $table->string('primary_email')->unique()->comment('Primary email address of the student');
            $table->string('secondary_email')->nullable()->comment('Secondary email address of the student');
            $table->string('primary_phone')->nullable()->comment('Primary phone number of the student');
            $table->string('secondary_phone')->nullable()->comment('Secondary phone number of the student');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->comment('Gender of the student');
            $table->date('dob')->nullable()->comment('Date of birth of the student');
            $table->string('password')->comment('Encrypted password');

            // Profile-related fields
            $table->string('picture')->nullable()->comment('Profile picture');
            $table->string('audience_type')->nullable()->comment('Type of audience the student belongs to');
            $table->boolean('is_active')->default(true)->comment('Indicates if the student is active');
            $table->boolean('is_blocked')->default(false)->comment('Indicates if the student is blocked');
            
            // Timestamps and soft deletes
            $table->softDeletes()->comment('Soft delete timestamp for logical deletion');
            $table->timestamps();

            // Indexes
            $table->index(['primary_email', 'is_active', 'is_blocked'], 'students_primary_email_active_blocked_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
