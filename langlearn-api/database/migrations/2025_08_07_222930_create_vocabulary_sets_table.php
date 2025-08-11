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
        Schema::create('vocabulary_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); 
            $table->enum('type', ['premade', 'custom']);
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced']);
            $table->integer('views')->default(0);
            $table->double('rating')->default(0.0);
            $table->string('title');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vocabulary_sets');
    }
};
