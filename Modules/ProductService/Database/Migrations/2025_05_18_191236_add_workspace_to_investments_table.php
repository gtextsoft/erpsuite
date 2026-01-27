<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\ProductService\Entities\Investment;

class AddWorkspaceToInvestmentsTable extends Migration
{
    public function up()
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->unsignedBigInteger('workspace')->after('currency')->default(7); // Adds workspace column after currency
            $table->foreign('workspace')->references('id')->on('work_spaces')->onDelete('cascade');
        });

        // Update existing records to set workspace = 7
        Investment::query()->update(['workspace' => 7]);
    }

    public function down()
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropForeign(['workspace']);
            $table->dropColumn('workspace');
        });
    }
}