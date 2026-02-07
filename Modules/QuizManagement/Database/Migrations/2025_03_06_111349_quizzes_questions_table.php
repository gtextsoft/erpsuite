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
        if (!Schema::hasTable('quizzes_questions')) {
            Schema::create('quizzes_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quiz_id')->onDelete('cascade');
                $table->foreignId('question_id')->onDelete('cascade');
                $table->foreignId('category_id')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes_questions');
    }
};
