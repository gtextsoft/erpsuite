<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryIdToQuizQuestionsTable extends Migration
{
    public function up()
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            // Make sure the referenced column type matches
            $table->unsignedBigInteger('category_id')->nullable()->after('quiz_id');

            // Foreign key constraint
            $table->foreign('category_id')
                  ->references('id')
                  ->on('question_categories')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
}

