<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            // Use UUID for scalability and distributed systems
            $table->uuid('refund_id')->primary();
            
            // Foreign Keys: Refund is linked to a user and a payment
            $table->uuid('user_id');
            $table->uuid('payment_id');

            // Refund Details
            $table->decimal('refund_amount', 10, 2);
            $table->enum('refund_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('refund_method', ['original_payment', 'bank_transfer', 'store_credit'])->nullable();
            $table->text('refund_reason')->nullable()->comment('Reason for refund request');
            $table->timestamp('processed_at')->nullable()->comment('Timestamp when refund was processed');

            // Soft Deletes for safe data removal
            $table->softDeletes();

            // Timestamps for auditing
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('payment_id')->references('payment_id')->on('payments')->onDelete('cascade');

            // Indexing for Performance
            $table->index(['user_id'], 'idx_user_id');
            $table->index(['payment_id'], 'idx_payment_id');
            $table->index(['refund_status'], 'idx_refund_status');
            $table->index(['refund_method'], 'idx_refund_method');
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
        Schema::dropIfExists('refunds');
    }
}
