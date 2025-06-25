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
        Schema::create('tomes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->json('alternate_titles')->nullable();
            $table->string('origin')->nullable();

            $table->foreignUuid('author_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->foreignUuid('language_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('current_owner_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->foreignUuid('last_known_location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->text('contents_summary')->nullable();
            $table->boolean('cursed')->default(false);
            $table->boolean('sentient')->default(false);
            $table->enum('danger_level', ['Low', 'Medium', 'High', 'Severe', 'Unknown'])
                ->default('Unknown');
            $table->string('artifact_type')->nullable();
            $table->string('cover_material')->nullable();
            $table->integer('pages')->nullable();
            $table->boolean('illustrated')->default(false);
            $table->json('notable_quotes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tomes');
    }
};
