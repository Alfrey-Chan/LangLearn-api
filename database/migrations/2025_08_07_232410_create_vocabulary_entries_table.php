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
        Schema::create('vocabulary_entries', function (Blueprint $table) {
            $table->id();
            $table->string('language_code');
            $table->foreign('language_code')->references('code')->on('languages')->cascadeOnDelete();

            // Core word data
            $table->string('word');
            $table->string('hiragana')->nullable();
            $table->string('romaji')->nullable();
            $table->string('pinyin')->nullable();
            $table->json('part_of_speech');
            $table->json('meanings');
            $table->string('additional_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vocabulary_entries');
    }
};
