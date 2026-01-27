<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvestmentsTable extends Migration
{
    public function up()
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->decimal('amount', 15, 2);
            $table->integer('duration_years');
            $table->decimal('interest_rate', 5, 2);
            $table->date('investment_date');
            $table->date('due_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('investments');
    }
}