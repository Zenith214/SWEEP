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
        Schema::create('recycling_log_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recycling_log_id')->constrained()->cascadeOnDelete();
            $table->enum('material_type', ['plastic', 'paper', 'glass', 'metal', 'cardboard', 'organic']);
            $table->decimal('weight', 8, 2);
            $table->timestamps();

            // Indexes
            $table->index('recycling_log_id');
            $table->index('material_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recycling_log_materials');
    }
};
