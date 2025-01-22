<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubSectionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sub_sections', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // Foreign key to sections table
            $table->unsignedBigInteger('section_id');
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->onDelete('cascade') // Cascade delete to remove related sub-sections
                ->onUpdate('cascade'); // Cascade updates to maintain integrity

            // Sub-section details
            $table->string('name')->comment('Name of the sub-section');
            $table->integer('time_allocated')->nullable()->comment('Time allocated for this sub-section in minutes');
            $table->unsignedInteger('order')->nullable()->comment('Order of the sub-section within the section');

            // Auditing fields
            $table->boolean('is_active')->default(true)->comment('Indicates if the sub-section is active');
            $table->softDeletes()->comment('Soft delete timestamp for logical deletion');

            // Timestamps
            $table->timestamps();

            // Indexes for optimization
            $table->index(['section_id', 'is_active'], 'sub_sections_section_id_is_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_sections');
    }
}
