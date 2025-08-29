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
        Schema::create('example_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('example_id');
            $table->string('user_id');
            $table->enum('example_type', ['sentence', 'dialogue']);
            $table->enum('vote_type', ['upvote', 'downvote']);
            $table->timestamps();

            $table->unique(['user_id', 'example_id', 'example_type']); // one vote per user per example
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('example_votes');
    }
};
