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
        Schema::create('audiences', function (Blueprint $table) {
            $table->id();

            // Audience details
            $table->string('name')->comment('Name of the audience group');
            $table->string('thumbnail_image')->nullable()->comment('Thumbnail image for the home page');
            $table->text('description')->nullable()->comment('Detailed description of the audience group');
            $table->string('cover_image')->nullable()->comment('Cover image for the details page');

            // Auditing and timestamps
            $table->boolean('is_active')->default(true)->comment('Indicates if the audience group is active');
            $table->softDeletes()->comment('Soft delete timestamp for logical deletion');
            $table->timestamps();

            // Indexes
            $table->index(['is_active'], 'audiences_is_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audiences');
    }
};
