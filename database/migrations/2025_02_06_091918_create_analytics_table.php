<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalyticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analytics', function (Blueprint $table) {
            // Use UUID for scalability and distributed systems
            $table->uuid('analytics_id')->primary();
            
            // Foreign Key: Each event belongs to a user
            $table->uuid('user_id');

            // Event Details
            $table->enum('event_type', ['login', 'test_attempt', 'answer_submission', 'score_update', 'subscription', 'page_view', 'coupon_use']);
            $table->json('event_details')->nullable()->comment('Stores additional metadata related to the event');
            $table->timestamp('event_timestamp')->default(now())->comment('Time when the event occurred');

            // Soft Deletes for safe data removal
            $table->softDeletes();

            // Timestamps for auditing
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');

            // Indexing for Performance
            $table->index(['user_id'], 'idx_user_id');
            $table->index(['event_type'], 'idx_event_type');
            $table->index(['event_timestamp'], 'idx_event_timestamp');
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
        Schema::dropIfExists('analytics');
    }
}
