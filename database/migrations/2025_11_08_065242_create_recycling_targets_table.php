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
        Schema::create('recycling_targets', function (Blueprint $table) {
            $table->id();
            $table->enum('material_type', ['plastic', 'paper', 'glass', 'metal', 'cardboard', 'organic', 'all'])->nullable();
            $table->decimal('target_weight', 10, 2);
            $table->date('month');
            $table->timestamps();

            // Unique index on month + material_type combination
            $table->unique(['month', 'material_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recycling_targets');
    }
};
