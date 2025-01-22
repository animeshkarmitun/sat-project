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
        Schema::create('audience_student', function (Blueprint $table) {
            $table->id();

            // Foreign key to audiences
            $table->unsignedBigInteger('audience_id');
            $table->foreign('audience_id')
                ->references('id')
                ->on('audiences')
                ->onDelete('cascade') // Cascade delete to maintain integrity
                ->onUpdate('cascade');

            // Foreign key to students
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade') // Cascade delete to maintain integrity
                ->onUpdate('cascade');

            // Timestamps for auditing
            $table->timestamps();

            // Indexes for optimization
            $table->index(['audience_id', 'student_id'], 'audience_student_audience_id_student_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audience_student');
    }
};
