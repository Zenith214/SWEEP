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
        Schema::table('collection_logs', function (Blueprint $table) {
            $table->foreignId('collection_id')->nullable()->after('assignment_id')->constrained('collections')->onDelete('cascade');
            
            // Add index for collection-based queries
            $table->index('collection_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collection_logs', function (Blueprint $table) {
            $table->dropForeign(['collection_id']);
            $table->dropIndex(['collection_id']);
            $table->dropColumn('collection_id');
        });
    }
};
