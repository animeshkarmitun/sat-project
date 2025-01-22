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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();

            // Package details
            $table->string('title')->comment('Package name');
            $table->string('audience_type')->nullable()->comment('Target audience type for the package');
            $table->text('description')->nullable()->comment('Detailed description of the package');
            $table->string('duration_text')->nullable()->comment('Human-readable duration text, e.g., \"1 Month\"');
            $table->integer('duration_days')->comment('Package duration in days');
            $table->decimal('price', 10, 2)->comment('Price of the package');
            $table->enum('type', ['daily', 'weekly', 'monthly'])->comment('Package frequency');

            // Auditing and timestamps
            $table->boolean('is_active')->default(true)->comment('Indicates if the package is active');
            $table->softDeletes()->comment('Soft delete timestamp for logical deletion');
            $table->timestamps();

            // Indexes
            $table->index(['audience_type', 'is_active'], 'packages_audience_type_is_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
