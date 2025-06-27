<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            // Add individual column indexes
            $table->index('response_time_ms');
            $table->index('cache_hit');

            // Add composite indexes for performance analysis
            $table->index(['created_at', 'response_time_ms'], 'api_logs_created_response_time_index');
            $table->index(['endpoint', 'method', 'created_at'], 'api_logs_endpoint_method_created_index');

            // Add security analysis indexes
            $table->index(['created_at', 'status_code', 'ip_address'], 'api_logs_created_status_ip_index');
            $table->index(['status_code', 'created_at'], 'api_logs_status_created_index');

            // Add error analysis index
            $table->index(['created_at', 'error_message'], 'api_logs_created_error_index');

            // Add traffic pattern indexes
            $table->index(['created_at', 'user_agent'], 'api_logs_created_user_agent_index');

            // Add cache performance index
            $table->index(['created_at', 'cache_hit'], 'api_logs_created_cache_index');

            // Add composite index for geographic analysis
            $table->index(['ip_address', 'created_at', 'endpoint'], 'api_logs_ip_created_endpoint_index');
        });
    }

    public function down(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            // Drop individual indexes
            $table->dropIndex(['response_time_ms']);
            $table->dropIndex(['cache_hit']);

            // Drop composite indexes by name
            $table->dropIndex('api_logs_created_response_time_index');
            $table->dropIndex('api_logs_endpoint_method_created_index');
            $table->dropIndex('api_logs_created_status_ip_index');
            $table->dropIndex('api_logs_status_created_index');
            $table->dropIndex('api_logs_created_error_index');
            $table->dropIndex('api_logs_created_user_agent_index');
            $table->dropIndex('api_logs_created_cache_index');
            $table->dropIndex('api_logs_ip_created_endpoint_index');
        });
    }
};
