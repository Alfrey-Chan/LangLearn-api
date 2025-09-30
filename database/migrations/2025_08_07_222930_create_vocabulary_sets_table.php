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
            $table->string('language_code');
            $table->foreign('language_code')->references('code')->on('languages');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); 
            $table->enum('difficulty', ['absolute_beginner', 'beginner', 'intermediate', 'advanced', '入門', '初級', '中級', '上級']);
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('image_url')->nullable();
            $table->integer('views')->default(0);
            $table->double('rating')->default(0.0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->string("category");
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
