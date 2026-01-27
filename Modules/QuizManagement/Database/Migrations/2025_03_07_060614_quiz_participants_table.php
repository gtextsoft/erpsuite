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
        if (!Schema::hasTable('quiz_participants')) {
            Schema::create('quiz_participants', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->string('mobile_no');
                $table->unsignedBigInteger('quiz_id'); // Foreign key
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('status');
                $table->integer('created_by');
                $table->integer('workspace');
                $table->timestamps(); // created_at & updated_at
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_participants');
    }
};
