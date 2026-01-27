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
        if (Schema::hasTable('zoom_meeting') && !Schema::hasColumn('zoom_meeting', 'user_id')) {
            Schema::table('zoom_meeting', function (Blueprint $table) {
                $table->integer('user_id')->nullable()->after('status');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zoom_meeting', function (Blueprint $table) {
            $table->integer('user_id')->default(0);
        });
    }
};
