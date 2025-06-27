<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->decimal('response_time_ms', 8, 2)->index();
            $table->integer('response_size')->nullable();
            $table->json('request_headers')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->boolean('cache_hit')->default(false)->index();
            $table->string('log_level', 20)->default('info')->index();
            $table->text('error_message')->nullable();
            $table->json('error_context')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Core time-based composite indexes
            $table->index(['created_at', 'status_code']);
            $table->index(['endpoint', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);

            // Performance analysis indexes
            $table->index(['created_at', 'response_time_ms']);
            $table->index(['endpoint', 'method', 'created_at']);

            // Security analysis indexes
            $table->index(['created_at', 'status_code', 'ip_address']);
            $table->index(['status_code', 'created_at']);

            // Cache performance index
            $table->index(['created_at', 'cache_hit']);

            // Composite index for geographic analysis
            $table->index(['ip_address', 'created_at', 'endpoint']);
        });

        // Add TEXT column indexes after table creation (MySQL specific)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE api_logs ADD INDEX api_logs_created_error_index (created_at, error_message(255))');
            DB::statement('ALTER TABLE api_logs ADD INDEX api_logs_created_user_agent_index (created_at, user_agent(255))');
        } else {
            // For other databases (SQLite, PostgreSQL)
            Schema::table('api_logs', function (Blueprint $table) {
                $table->index(['created_at', 'error_message'], 'api_logs_created_error_index');
                $table->index(['created_at', 'user_agent'], 'api_logs_created_user_agent_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
