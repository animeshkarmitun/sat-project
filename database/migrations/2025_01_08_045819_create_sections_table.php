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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();

            // Link to exams
            $table->foreignId('exam_id')
                ->constrained()
                ->onDelete('cascade') // Cascade delete to maintain integrity
                ->onUpdate('cascade');

            // Section details
            $table->string('title')->comment('Title of the section');
            $table->text('description')->nullable()->comment('Description of the section');
            $table->string('subject')->nullable()->comment('Subject name, e.g., Physics, Math, Reading');
            $table->unsignedInteger('order')->nullable()->comment('Order of the section within the exam');
            $table->integer('time_allocated')->nullable()->comment('Time allocated for the section in minutes');

            // Status
            $table->boolean('is_active')->default(true)->comment('Indicates if the section is active');

            // Auditing fields
            $table->softDeletes()->comment('Soft delete timestamp for logical deletion');
            $table->timestamps();

            // Indexes for optimization
            $table->index(['exam_id', 'is_active'], 'sections_exam_id_is_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
