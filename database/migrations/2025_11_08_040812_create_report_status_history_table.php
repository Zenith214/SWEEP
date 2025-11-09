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
        Schema::create('report_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->enum('old_status', ['pending', 'in_progress', 'resolved', 'closed'])->nullable();
            $table->enum('new_status', ['pending', 'in_progress', 'resolved', 'closed']);
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('report_id');
            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_status_history');
    }
};
