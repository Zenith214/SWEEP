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
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name', 255);
            $table->enum('frequency', ['daily', 'weekly', 'monthly']);
            $table->json('metrics')->nullable();
            $table->enum('format', ['pdf', 'csv'])->default('pdf');
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamp('next_generation_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index(['is_active', 'next_generation_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
