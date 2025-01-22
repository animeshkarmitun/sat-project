<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamSectionPivotTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_section', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // Foreign key to exams table
            $table->unsignedBigInteger('exam_id');
            $table->foreign('exam_id')
                ->references('id')
                ->on('exams')
                ->onDelete('cascade') // Cascade delete to maintain integrity
                ->onUpdate('cascade');

            // Foreign key to sections table
            $table->unsignedBigInteger('section_id');
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->onDelete('cascade') // Cascade delete to maintain integrity
                ->onUpdate('cascade');

            // Timestamps for auditing
            $table->timestamps();

            // Indexes for optimization
            $table->index(['exam_id', 'section_id'], 'exam_section_exam_id_section_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_section');
    }
}
