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
        if (!Schema::hasTable('quiz_results')) {
            Schema::create('quiz_results', function (Blueprint $table) {
                $table->id();
                $table->integer('participant_id');
                $table->integer('quiz_id');
                $table->integer('score');
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('status');
                $table->string('total_time_teken')->nullable();
                $table->date('attempt_date');
                $table->integer('created_by');
                $table->integer('workspace');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
