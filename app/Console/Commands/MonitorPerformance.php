<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MonitorPerformance extends Command
{
    protected $signature = 'activities:monitor-performance';
    protected $description = 'Monitor activities module performance';

    public function handle()
    {
        $this->info('Activities Module Performance Report');
        $this->info('=====================================');

        // Check slow queries
        $this->checkSlowQueries();

        // Check cache hit rates
        $this->checkCacheStats();

        // Check database size
        $this->checkDatabaseSize();

        // Check index usage
        $this->checkIndexUsage();
    }

    private function checkSlowQueries()
    {
        $this->info("\n1. Slow Queries Analysis:");
        
        try {
            $slowQueries = DB::select("
                SELECT sql_text, avg_timer_wait/1000000000 as avg_time_seconds, count_star as executions
                FROM performance_schema.events_statements_summary_by_digest 
                WHERE avg_timer_wait > 1000000000 
                AND sql_text LIKE '%activities%' 
                ORDER BY avg_timer_wait DESC 
                LIMIT 10
            ");

            if (empty($slowQueries)) {
                $this->line('✓ No slow queries detected');
            } else {
                foreach ($slowQueries as $query) {
                    $this->warn("⚠ Slow query: {$query->avg_time_seconds}s avg");
                }
            }
        } catch (\Exception $e) {
            $this->warn('Could not check slow queries: ' . $e->getMessage());
        }
    }

    private function checkCacheStats()
    {
        $this->info("\n2. Cache Statistics:");
        
        $cacheKeys = [
            'rehab_categories_admin',
            'activity_stats_admin_*',
            'popular_categories'
        ];

        foreach ($cacheKeys as $key) {
            if (Cache::has($key)) {
                $this->line("✓ Cache hit: {$key}");
            } else {
                $this->warn("⚠ Cache miss: {$key}");
            }
        }
    }

    private function checkDatabaseSize()
    {
        $this->info("\n3. Database Size Analysis:");
        
        $tables = [
            'activities',
            'rehabilitation_activities',
            'activity_sessions',
            'activity_attendances',
            'rehabilitation_objectives'
        ];

        foreach ($tables as $table) {
            try {
                $size = DB::select("
                    SELECT 
                        table_name,
                        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                        table_rows
                    FROM information_schema.tables 
                    WHERE table_schema = DATABASE() 
                    AND table_name = ?
                ", [$table]);

                if (!empty($size)) {
                    $s = $size[0];
                    $this->line("{$s->table_name}: {$s->size_mb} MB ({$s->table_rows} rows)");
                }
            } catch (\Exception $e) {
                $this->warn("Could not check size for {$table}");
            }
        }
    }

    private function checkIndexUsage()
    {
        $this->info("\n4. Index Usage Analysis:");
        
        try {
            $unusedIndexes = DB::select("
                SELECT 
                    t.table_name,
                    t.index_name,
                    t.column_name
                FROM information_schema.statistics t
                LEFT JOIN performance_schema.table_io_waits_summary_by_index_usage p 
                    ON t.table_schema = p.object_schema 
                    AND t.table_name = p.object_name 
                    AND t.index_name = p.index_name
                WHERE t.table_schema = DATABASE()
                    AND t.table_name IN ('activities', 'rehabilitation_activities', 'activity_sessions')
                    AND p.index_name IS NULL
                    AND t.index_name != 'PRIMARY'
            ");

            if (empty($unusedIndexes)) {
                $this->line('✓ All indexes are being used');
            } else {
                $this->warn('⚠ Unused indexes detected:');
                foreach ($unusedIndexes as $index) {
                    $this->line("  - {$index->table_name}.{$index->index_name}");
                }
            }
        } catch (\Exception $e) {
            $this->warn('Could not check index usage: ' . $e->getMessage());
        }
    }
}