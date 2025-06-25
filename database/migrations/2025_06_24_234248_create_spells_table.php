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
        Schema::create('spells', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('effect')->nullable();
            $table->enum('danger_level', ['Low', 'Medium', 'High', 'Severe', 'Unknown'])
                ->default('Unknown');
            $table->foreignUuid('tome_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spells');
    }
};
