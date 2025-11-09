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
        Schema::create('dismissed_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('alert_category'); // e.g., 'unassigned_route', 'overdue_report', 'truck_maintenance'
            $table->string('alert_identifier'); // e.g., assignment_id, report_id, truck_id
            $table->timestamp('dismissed_at');
            $table->timestamps();

            // Ensure a user can only dismiss the same alert once
            $table->unique(['user_id', 'alert_category', 'alert_identifier']);
            
            // Index for efficient lookups
            $table->index(['user_id', 'alert_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dismissed_alerts');
    }
};
