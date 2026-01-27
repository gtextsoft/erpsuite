<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyToInvestmentsTable extends Migration
{
    public function up()
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->string('currency', 3)->default('NGN')->after('amount'); // Adds currency column after amount
        });
    }

    public function down()
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
}