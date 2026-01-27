<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gtexthomes_clients', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number');
            $table->string('name');
            $table->string('house');
            $table->decimal('amount_agreed', 15, 2);
            $table->decimal('amount_paid', 15, 2);
            $table->decimal('land_rec', 15, 2);
            $table->string('email')->nullable();
            $table->string('phone_number');
            $table->integer('workspace')->default(10);
            $table->string('currency')->default('NGN');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gtexthomes_clients');
    }
};