<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            // Use UUID for scalability and distributed systems
            $table->uuid('subscription_id')->primary();
            
            // Foreign Key: Each subscription belongs to a user
            $table->uuid('user_id');

            // Subscription Details
            $table->enum('plan_type', ['free', 'basic', 'premium'])->default('free');
            $table->timestamp('start_date')->default(now());
            $table->timestamp('end_date')->nullable()->comment('Subscription expiration date');

            // Payment Details
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->enum('payment_gateway', ['credit_card', 'paypal', 'stripe', 'bank_transfer'])->nullable();
            $table->string('transaction_id', 255)->nullable()->unique()->comment('Transaction reference ID');

            // Soft Deletes for safe data removal
            $table->softDeletes();

            // Timestamps for auditing
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');

            // Indexing for Performance
            $table->index(['user_id'], 'idx_user_id');
            $table->index(['plan_type'], 'idx_plan_type');
            $table->index(['payment_status'], 'idx_payment_status');
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
        Schema::dropIfExists('subscriptions');
    }
}
