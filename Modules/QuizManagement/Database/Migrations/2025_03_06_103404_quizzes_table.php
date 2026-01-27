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
        if (!Schema::hasTable('quizzes')) {
            Schema::create('quizzes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category_id');
                $table->integer('total_marks');
                $table->integer('passing_marks');
                $table->integer('per_qus_time');
                $table->integer('total_time_duration');
                $table->string('color');
                $table->integer('status');
                $table->text('image')->nullable();
                $table->integer('workspace')->nullable();
                $table->integer('created_by');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
