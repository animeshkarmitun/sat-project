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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            // Coupon details
            $table->string('name')->unique()->comment('Unique coupon name or code');
            $table->integer('total_usable')->comment('Maximum number of times the coupon can be used');
            $table->integer('total_used')->default(0)->comment('Tracks how many times the coupon has been used');
            $table->enum('discount_type', ['percentage', 'fixed'])->comment('Type of discount: percentage or fixed value');
            $table->decimal('discount_value', 10, 2)->comment('Value of the discount');
            $table->decimal('max_discount', 10, 2)->nullable()->comment('Maximum discount amount for percentage-based coupons');
            $table->timestamp('expiry_date')->comment('Expiration date of the coupon');

            // Auditing and timestamps
            $table->softDeletes()->comment('Soft delete timestamp for logical deletion');
            $table->timestamps();

            // Indexes
            $table->index(['name', 'expiry_date'], 'coupons_name_expiry_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
