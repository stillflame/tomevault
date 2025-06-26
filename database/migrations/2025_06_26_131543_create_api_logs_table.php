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
            $table->decimal('response_time_ms', 8, 2);
            $table->integer('response_size')->nullable();
            $table->json('request_headers')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->boolean('cache_hit')->default(false);
            $table->string('log_level', 20)->default('info')->index();
            $table->text('error_message')->nullable();
            $table->json('error_context')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index(['created_at', 'status_code']);
            $table->index(['endpoint', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
