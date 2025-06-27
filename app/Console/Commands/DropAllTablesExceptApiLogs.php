<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropAllTablesExceptApiLogs extends Command
{
    protected $signature = 'db:drop-all-except-api-logs';
    protected $description = 'Drop all tables except api_logs';

    public function handle(): int
    {
        $this->warn('Dropping all tables except `api_logs`...');

        $tableKey = 'Tables_in_' . DB::getDatabaseName();

        $tables = DB::select('SHOW TABLES');

        $tablesToDrop = collect($tables)
            ->pluck($tableKey)
            ->filter(static fn ($table) => $table !== 'api_logs')
            ->values()
            ->all();

        Schema::disableForeignKeyConstraints();

        foreach ($tablesToDrop as $table) {
            Schema::drop($table);
            $this->line("Dropped: {$table}");
        }

        Schema::enableForeignKeyConstraints();

        $this->info('All tables dropped except `api_logs`.');
        return self::SUCCESS;
    }
}
