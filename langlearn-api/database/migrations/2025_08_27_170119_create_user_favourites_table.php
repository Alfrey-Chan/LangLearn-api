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
        Schema::create('user_favourites', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_uid');
            $table->foreign('firebase_uid')->references('firebase_uid')->on('users');
            $table->foreignId('vocabulary_entry_id')->nullable()->constrained()->onDeleteCascade();
            $table->foreignId('vocabulary_set_id')->nullable()->constrained()->onDeleteCascade();
            $table->timestamps();
            
            // Unique constraint to prevent duplicates
            $table->unique(['firebase_uid', 'vocabulary_entry_id', 'vocabulary_set_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favourites');
    }
};
