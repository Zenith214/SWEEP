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
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_report_id')->constrained('scheduled_reports')->onDelete('cascade');
            $table->string('file_path', 500);
            $table->timestamp('generated_at');
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('scheduled_report_id');
            $table->index('generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_reports');
    }
};
