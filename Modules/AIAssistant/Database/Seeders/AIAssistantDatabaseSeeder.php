<?php

namespace Modules\AIAssistant\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\AIAssistant\Database\Seeders\AIAssistantTemplateTableSeeder;
use Modules\AIAssistant\Database\Seeders\MarketPlaceSeederTableSeeder;
use Nwidart\Modules\Facades\Module;

class AIAssistantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Seed base templates
        $this->call(AIAssistantTemplateTableSeeder::class);

        // Optionally seed marketplace data if LandingPage module is enabled
        if (Module::has('LandingPage') && Module::find('LandingPage')->isEnabled()) {
            $this->call(MarketPlaceSeederTableSeeder::class);
        }
    }
}
