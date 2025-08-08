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
        Schema::create('user_statistics', function (Blueprint $table) {
            $table->string('user_id')->primary();
            $table->integer('day_streak')->default(0);
            $table->integer('total_quizzes')->default(0);
            $table->decimal('average_score', 5, 2)->default(0.00);
            $table->date('last_activity_date')->nullable(); // To calculate streak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_statistics');
    }
};
