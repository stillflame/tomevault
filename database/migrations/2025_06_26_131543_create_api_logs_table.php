<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('request_id')->index();
            $table->string('method', 10);
            $table->text('url');
            $table->string('endpoint')->index();
            $table->ipAddress('ip_address')->index();
            $table->text('user_agent')->nullable();
            $table->uuid('user_id')->nullable()->index();
            $table->string('user_type')->nullable();
            $table->integer('status_code')->index();
            $table->decimal('response_time_ms', 8, 2)->index(); // Added individual index
            $table->integer('response_size')->nullable();
            $table->json('request_headers')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->boolean('cache_hit')->default(false)->index(); // Added individual index
            $table->string('log_level', 20)->default('info')->index();
            $table->text('error_message')->nullable();
            $table->json('error_context')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Core time-based queries (your existing ones are good)
            $table->index(['created_at', 'status_code']);
            $table->index(['endpoint', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);

            // Performance analysis indexes
            $table->index(['created_at', 'response_time_ms']); // For percentile calculations
            $table->index(['endpoint', 'method', 'created_at']); // For endpoint stats grouping

            // Security analysis indexes
            $table->index(['created_at', 'status_code', 'ip_address']); // For suspicious IP detection
            $table->index(['status_code', 'created_at']); // For auth failures (401, 403)

            // Error analysis indexes
            $table->index(['created_at', 'error_message']); // For error message grouping

            // Traffic pattern indexes
            $table->index(['created_at', 'user_agent']); // For user agent analysis

            // Cache performance index
            $table->index(['created_at', 'cache_hit']); // For cache hit rate calculation

            // Composite index for geographic analysis (IP + time)
            $table->index(['ip_address', 'created_at', 'endpoint']); // For detailed IP analysis

            // Add individual column indexes
            $table->index('response_time_ms');
            $table->index('cache_hit');

            // Add composite indexes for performance analysis
            $table->index(['created_at', 'response_time_ms'], 'api_logs_created_response_time_index');
            $table->index(['endpoint', 'method', 'created_at'], 'api_logs_endpoint_method_created_index');

            // Add security analysis indexes
            $table->index(['created_at', 'status_code', 'ip_address'], 'api_logs_created_status_ip_index');
            $table->index(['status_code', 'created_at'], 'api_logs_status_created_index');

            // Add error analysis index (TEXT columns need key length in MySQL)
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                // Use raw SQL for MySQL TEXT column indexing
                DB::statement('ALTER TABLE api_logs ADD INDEX api_logs_created_error_index (created_at, error_message(255))');
                DB::statement('ALTER TABLE api_logs ADD INDEX api_logs_created_user_agent_index (created_at, user_agent(255))');
            } else {
                // For other databases (SQLite, PostgreSQL)
                $table->index(['created_at', 'error_message'], 'api_logs_created_error_index');
                $table->index(['created_at', 'user_agent'], 'api_logs_created_user_agent_index');
            }

            // Add cache performance index
            $table->index(['created_at', 'cache_hit'], 'api_logs_created_cache_index');

            // Add composite index for geographic analysis
            $table->index(['ip_address', 'created_at', 'endpoint'], 'api_logs_ip_created_endpoint_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
