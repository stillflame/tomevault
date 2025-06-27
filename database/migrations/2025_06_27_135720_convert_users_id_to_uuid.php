<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */


    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid()->nullable()->after('id'); // Step 1: add uuid column
        });

        // Step 2: backfill UUIDs
        User::query()->each(static function ($user) {
            $user->uuid = (string)Str::uuid();
            $user->save();
        });

        // Step 3: update dependent tables
        Schema::table('tomes', function (Blueprint $table) {
            $table->uuid('created_by_uuid')->nullable()->after('created_by');
        });

        // Step 4: copy foreign key data
        DB::table('tomes')->update([
            'created_by_uuid' => DB::raw("(SELECT uuid FROM users WHERE users.id = tomes.created_by)")
        ]);

        // Step 5: drop old foreign key + columns
        Schema::table('tomes', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });

        // Step 6: rename and re-link
        Schema::table('tomes', function (Blueprint $table) {
            $table->renameColumn('created_by_uuid', 'created_by');
            $table->foreign('created_by')->references('uuid')->on('users')->onDelete('set null');
        });

        // Step 7: drop old PK + set uuid as new PK
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id');
            $table->renameColumn('uuid', 'id');
            $table->primary('id');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            //
        });
    }
};
