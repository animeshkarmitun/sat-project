<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            // Use UUID for scalability and distributed systems
            $table->uuid('payment_id')->primary();
            
            // Foreign Keys: Payment is linked to a user and a subscription (if applicable)
            $table->uuid('user_id');
            $table->uuid('subscription_id')->nullable()->comment('If the payment is for a subscription');

            // Payment Details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->enum('payment_method', ['credit_card', 'paypal', 'stripe', 'bank_transfer', 'crypto'])->nullable();
            $table->string('transaction_id', 255)->unique()->comment('Unique transaction reference ID');
            $table->timestamp('payment_date')->default(now());

            // Soft Deletes for safe data removal
            $table->softDeletes();

            // Timestamps for auditing
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('subscription_id')->references('subscription_id')->on('subscriptions')->onDelete('set null');

            // Indexing for Performance
            $table->index(['user_id'], 'idx_user_id');
            $table->index(['subscription_id'], 'idx_subscription_id');
            $table->index(['payment_status'], 'idx_payment_status');
            $table->index(['payment_method'], 'idx_payment_method');
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
        Schema::dropIfExists('payments');
    }
}
