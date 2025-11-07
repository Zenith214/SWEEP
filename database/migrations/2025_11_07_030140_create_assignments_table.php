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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained('trucks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('route_id')->constrained('routes')->onDelete('cascade');
            $table->date('assignment_date');
            $table->enum('status', ['active', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraints for active assignments
            $table->unique(['truck_id', 'assignment_date', 'status'], 'unique_truck_assignment');
            $table->unique(['user_id', 'assignment_date', 'status'], 'unique_user_assignment');
            
            // Indexes for performance
            $table->index('assignment_date');
            $table->index(['truck_id', 'assignment_date']);
            $table->index(['user_id', 'assignment_date']);
            $table->index(['route_id', 'assignment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
