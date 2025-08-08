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
            $table->foreignId('language_id')->constrained();

            // Core word data
            $table->string('word');
            $table->string('hiragana')->nullable();
            $table->string('romaji')->nullable();
            $table->string('pinyin')->nullable();

            // JSON arrays 
            $table->json('meanings');
            $table->json('sentences_examples');
            $table->json('dialogue_examples');

            // Stats & metadata
            $table->integer('upvotes')->default(0);
            $table->integer('downvotes')->default(0);
            $table->integer('views')->default(0);
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
