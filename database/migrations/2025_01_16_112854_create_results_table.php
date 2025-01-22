<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // Foreign key to students table
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade') // Cascade delete to maintain integrity
                ->onUpdate('cascade');

            // Foreign key to exams table
            $table->unsignedBigInteger('exam_id')->nullable();
            $table->foreign('exam_id')
                ->references('id')
                ->on('exams')
                ->onDelete('set null') // Set null if the exam is deleted
                ->onUpdate('cascade');

            // Foreign key to sections table
            $table->unsignedBigInteger('section_id')->nullable();
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // Foreign key to sub_sections table
            $table->unsignedBigInteger('sub_section_id')->nullable();
            $table->foreign('sub_section_id')
                ->references('id')
                ->on('sub_sections')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // Result details
            $table->integer('score')->nullable()->comment('Score achieved by the student');
            $table->float('percentage', 5, 2)->nullable()->comment('Percentage score');
            $table->integer('time_taken')->nullable()->comment('Time taken to complete the exam in minutes');

            // Timestamps for auditing
            $table->timestamps();

            // Indexes for optimization
            $table->index(['student_id', 'exam_id'], 'results_student_id_exam_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
}
