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
        Schema::table('recycling_log_materials', function (Blueprint $table) {
            $table->enum('unit', ['kg', 'lbs', 'tons'])->default('kg')->after('weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recycling_log_materials', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
