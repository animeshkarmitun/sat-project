<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // Use CHAR(36) for storing UUIDs, Laravel will generate UUIDs on insert
            $table->uuid('user_id')->primary();

            // User details
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password');
            $table->string('password_salt'); // Used for additional security
            $table->enum('role', ['student', 'admin', 'teacher'])->default('student');

            // Account status
            $table->boolean('is_active')->default(true);
            $table->string('ip_address', 45)->nullable(); // Stores IPv4 or IPv6 address

            // Soft delete with Laravel's SoftDeletes trait
            $table->softDeletes();

            // Last login tracking
            $table->timestamp('last_login')->nullable();

            // Profile Picture (Default to a generic avatar)
            $table->string('profile_picture_url', 2083)->default('https://example.com/default-avatar.png');

            // Timestamps (auto-managing `created_at` & `updated_at`)
            $table->timestamps();

            // Indexes for optimization
            $table->index(['email'], 'idx_email');
            $table->index(['role'], 'idx_role');
            $table->index(['deleted_at'], 'idx_deleted_at'); // Optimized soft delete queries
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
