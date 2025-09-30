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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['multiple_choice', 'fill_blank', 'translation', 'sentence_creation', 'word_rearrangement']);
            $table->string('question');
            $table->string('target_word');
            $table->string('correct_answer')->nullable();
            $table->boolean('requires_feedback');
            $table->integer('points');

            $table->json('options')->nullable();
            $table->json('word_bank')->nullable();
            $table->json('acceptable_answers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
