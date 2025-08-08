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
        Schema::create('user_notes', function (Blueprint $table) {
            $table->string('user_id'); // Firebase UID
            $table->unsignedBigInteger('item_id');
            $table->enum('note_type', ['vocabulary_set', 'vocabulary_entry']);
            $table->enum('save_type', ['save', 'favourite']);
            $table->timestamps();

            $table->primary(['user_id', 'note_type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notes');
    }
};
