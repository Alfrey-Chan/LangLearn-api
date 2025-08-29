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
        Schema::create('vocabulary_entry_vocabulary_set', function (Blueprint $table) {
            $table->foreignId('vocabulary_set_id')->constrained()->onDelete('cascade');
            $table->foreignId('vocabulary_entry_id')->constrained()->onDelete('cascade');

            $table->primary(['vocabulary_set_id', 'vocabulary_entry_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vocabulary_entry_vocabulary_set');
    }
};
