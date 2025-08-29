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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->foreignId('vocabulary_set_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned();
            $table->timestamps();

            $table->unique(['user_id', 'vocabulary_set_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
