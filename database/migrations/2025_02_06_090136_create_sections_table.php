<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            // Use UUID for scalability and distributed systems
            $table->uuid('section_id')->primary();
            
            // Foreign Key: Each section belongs to a test
            $table->uuid('test_id');

            // Section Details
            $table->string('section_name', 255);
            $table->integer('section_order')->unsigned()->comment('Defines the order of sections in a test');
            $table->integer('time_limit')->unsigned()->nullable()->comment('Time limit for this section in minutes');

            // Audit Fields: Tracking who created and updated the section
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();

            // Soft Deletes for safe data removal
            $table->softDeletes();

            // Timestamps for auditing
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('test_id')->references('test_id')->on('tests')->onDelete('cascade');
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('user_id')->on('users')->onDelete('set null');

            // Indexing for Performance
            $table->index(['test_id'], 'idx_test_id');
            $table->index(['deleted_at'], 'idx_deleted_at');
            $table->index(['section_order'], 'idx_section_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sections');
    }
}
