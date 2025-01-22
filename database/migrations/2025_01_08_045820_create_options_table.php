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
        Schema::create('options', function (Blueprint $table) {
            $table->id();

            // Link to questions
            $table->foreignId('question_id')
                ->constrained()
                ->onDelete('cascade') // Cascade delete to maintain integrity
                ->onUpdate('cascade')
                ->comment('Link to questions table');

            // Option details
            $table->string('option_text')->comment('Text of the option');
            $table->boolean('is_correct')->default(false)->comment('Indicates if this option is the correct answer');
            $table->boolean('is_active')->default(true)->comment('Indicates if the option is active and visible');
            $table->unsignedInteger('order')->nullable()->comment('Order of the option within the question');

            // Auditing fields
            $table->softDeletes()->comment('Soft delete timestamp for logical deletion');
            $table->timestamps();

            // Indexes for optimization
            $table->index(['question_id', 'is_active'], 'options_question_id_is_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('options');
    }
};
