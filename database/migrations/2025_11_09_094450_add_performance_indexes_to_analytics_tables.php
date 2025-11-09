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
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        
        // Helper function to check if index exists (database-agnostic)
        $indexExists = function($table, $indexName) use ($connection, $driver) {
            try {
                if ($driver === 'sqlite') {
                    // SQLite: Use pragma index_list
                    $indexes = $connection->select("PRAGMA index_list({$table})");
                    foreach ($indexes as $index) {
                        if ($index->name === $indexName) {
                            return true;
                        }
                    }
                    return false;
                } else {
                    // MySQL/MariaDB: Use SHOW INDEX
                    $indexes = $connection->select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
                    return count($indexes) > 0;
                }
            } catch (\Exception $e) {
                // If we can't check, assume it doesn't exist
                return false;
            }
        };
        
        // Indexes for collection_logs table
        Schema::table('collection_logs', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('collection_logs', 'idx_collection_logs_created_at')) {
                $table->index('created_at', 'idx_collection_logs_created_at');
            }
            if (!$indexExists('collection_logs', 'idx_collection_logs_status_created')) {
                $table->index(['status', 'created_at'], 'idx_collection_logs_status_created');
            }
        });

        // Indexes for assignments table
        Schema::table('assignments', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('assignments', 'idx_assignments_date_crew')) {
                $table->index(['assignment_date', 'user_id'], 'idx_assignments_date_crew');
            }
            if (!$indexExists('assignments', 'idx_assignments_date_status')) {
                $table->index(['assignment_date', 'status'], 'idx_assignments_date_status');
            }
            if (!$indexExists('assignments', 'idx_assignments_route_date')) {
                $table->index(['route_id', 'assignment_date'], 'idx_assignments_route_date');
            }
        });

        // Indexes for reports table
        Schema::table('reports', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('reports', 'idx_reports_created_status')) {
                $table->index(['created_at', 'status'], 'idx_reports_created_status');
            }
            if (!$indexExists('reports', 'idx_reports_route_created')) {
                $table->index(['route_id', 'created_at'], 'idx_reports_route_created');
            }
            if (!$indexExists('reports', 'idx_reports_status_created')) {
                $table->index(['status', 'created_at'], 'idx_reports_status_created');
            }
        });

        // Indexes for recycling_logs table
        Schema::table('recycling_logs', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('recycling_logs', 'idx_recycling_logs_created_at')) {
                $table->index('created_at', 'idx_recycling_logs_created_at');
            }
            if (!$indexExists('recycling_logs', 'idx_recycling_logs_assignment_created')) {
                $table->index(['assignment_id', 'created_at'], 'idx_recycling_logs_assignment_created');
            }
        });

        // Indexes for trucks table
        Schema::table('trucks', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('trucks', 'idx_trucks_operational_status')) {
                $table->index('operational_status', 'idx_trucks_operational_status');
            }
        });

        // Indexes for routes table
        Schema::table('routes', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('routes', 'idx_routes_is_active')) {
                $table->index('is_active', 'idx_routes_is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from collection_logs table
        Schema::table('collection_logs', function (Blueprint $table) {
            $table->dropIndex('idx_collection_logs_created_at');
            $table->dropIndex('idx_collection_logs_status_created');
        });

        // Drop indexes from assignments table
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('idx_assignments_date_crew');
            $table->dropIndex('idx_assignments_date_status');
            $table->dropIndex('idx_assignments_route_date');
        });

        // Drop indexes from reports table
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('idx_reports_created_status');
            $table->dropIndex('idx_reports_route_created');
            $table->dropIndex('idx_reports_status_created');
        });

        // Drop indexes from recycling_logs table
        Schema::table('recycling_logs', function (Blueprint $table) {
            $table->dropIndex('idx_recycling_logs_created_at');
            $table->dropIndex('idx_recycling_logs_assignment_created');
        });

        // Drop indexes from trucks table
        Schema::table('trucks', function (Blueprint $table) {
            $table->dropIndex('idx_trucks_operational_status');
        });

        // Drop indexes from routes table
        Schema::table('routes', function (Blueprint $table) {
            $table->dropIndex('idx_routes_is_active');
        });
    }
};
