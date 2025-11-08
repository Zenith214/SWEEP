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
        Schema::create('collection_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->unique()->constrained('assignments')->onDelete('cascade');
            $table->dateTime('completion_time')->nullable();
            $table->enum('status', ['pending', 'completed', 'incomplete', 'issue_reported'])->default('pending');
            $table->string('issue_type', 100)->nullable();
            $table->text('issue_description')->nullable();
            $table->tinyInteger('completion_percentage')->nullable();
            $table->text('crew_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('assignment_id');
            $table->index('status');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_logs');
    }
};
