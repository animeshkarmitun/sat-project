<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateExamsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Add new columns to enhance exams functionality
            $table->string('code')->nullable()->unique()->after('title')->comment('Unique code for the exam');
            $table->enum('type', ['practice', 'official'])->default('practice')->after('description')->comment('Type of the exam: practice or official');
            $table->string('audience')->nullable()->after('type')->comment('Target audience for the exam, e.g., SAT, GRE');
            $table->integer('total_marks')->nullable()->after('duration')->comment('Total marks for the exam');
            $table->integer('passing_marks')->nullable()->after('total_marks')->comment('Passing marks for the exam');
            $table->boolean('is_published')->default(false)->after('is_active')->comment('Indicates if the exam is published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Drop the newly added columns
            $table->dropColumn('code');
            $table->dropColumn('type');
            $table->dropColumn('audience');
            $table->dropColumn('total_marks');
            $table->dropColumn('passing_marks');
            $table->dropColumn('is_published');
        });
    }
}
