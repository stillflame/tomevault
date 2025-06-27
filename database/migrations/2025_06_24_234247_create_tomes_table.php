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
            $table->string('title')->index(); // Add index directly
            $table->string('slug')->nullable()->unique();
            $table->json('alternate_titles')->nullable();
            $table->string('origin')->nullable()->index(); // Add index directly

            $table->foreignUuid('author_id')->nullable()->constrained('characters')->nullOnDelete()->index();
            $table->foreignUuid('language_id')->nullable()->constrained()->nullOnDelete()->index();
            $table->foreignUuid('current_owner_id')->nullable()->constrained('characters')->nullOnDelete()->index();
            $table->foreignUuid('last_known_location_id')->nullable()->constrained('locations')->nullOnDelete()->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete()->index();

            $table->text('contents_summary')->nullable();
            $table->boolean('cursed')->default(false)->index();
            $table->boolean('sentient')->default(false)->index();
            $table->enum('danger_level', ['Low', 'Medium', 'High', 'Severe', 'Unknown'])
                ->default('Unknown')->index();
            $table->string('artifact_type')->nullable()->index();
            $table->string('cover_material')->nullable();
            $table->integer('pages')->nullable();
            $table->boolean('illustrated')->default(false)->index();
            $table->json('notable_quotes')->nullable();
            $table->timestamps();

            // Add composite indexes
            $table->index('created_at'); // For ordering/filtering by creation date
            $table->index('updated_at'); // For ordering by last modified
            $table->index(['created_at', 'title']); // For paginated lists ordered by date
            $table->index(['danger_level', 'cursed']); // For security/danger filtering
            $table->index(['author_id', 'created_at']); // For author's tomes chronologically
            $table->index(['current_owner_id', 'created_at']); // For owner's tomes chronologically
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
