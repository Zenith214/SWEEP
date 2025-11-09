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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->onDelete('cascade');
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->date('collection_date');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'incomplete', 'cancelled'])->default('scheduled');
            $table->dateTime('completion_time')->nullable();
            $table->timestamps();
            
            // Indexes for performance optimization
            $table->index(['collection_date', 'status']);
            $table->index(['route_id', 'collection_date']);
            $table->index('assignment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
