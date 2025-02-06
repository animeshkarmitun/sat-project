<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            // Use UUID for scalability and distributed systems
            $table->uuid('coupon_id')->primary();
            
            // Coupon Details
            $table->string('coupon_code', 50)->unique();
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_value', 10, 2)->comment('Percentage or fixed amount discount');
            $table->decimal('min_purchase_amount', 10, 2)->nullable()->comment('Minimum purchase required to use the coupon');
            $table->integer('max_redemptions')->unsigned()->default(1)->comment('Max times the coupon can be used');
            $table->integer('redemptions_used')->unsigned()->default(0)->comment('Times the coupon has been used');

            // Expiration & Status
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['active', 'expired', 'disabled'])->default('active');

            // Redeemed By User (Optional)
            $table->uuid('redeemed_by')->nullable()->comment('User who redeemed the coupon');

            // Soft Deletes for safe data removal
            $table->softDeletes();

            // Timestamps for auditing
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('redeemed_by')->references('user_id')->on('users')->onDelete('set null');

            // Indexing for Performance
            $table->index(['coupon_code'], 'idx_coupon_code');
            $table->index(['discount_type'], 'idx_discount_type');
            $table->index(['status'], 'idx_status');
            $table->index(['redeemed_by'], 'idx_redeemed_by');
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
        Schema::dropIfExists('coupons');
    }
}
