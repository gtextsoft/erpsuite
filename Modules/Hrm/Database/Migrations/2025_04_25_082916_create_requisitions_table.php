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
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->foreignId('first_approver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('gcoo_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('accountant_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'first_approved', 'gcoo_approved', 'accountant_approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};