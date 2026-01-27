<?php

namespace Modules\QuizManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class QuizManagementDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call(MarketPlaceSeederTableSeeder::class);
        if (module_is_active('LandingPage')) {
            $this->call(PermissionTableSeeder::class);
        };
    }
}
