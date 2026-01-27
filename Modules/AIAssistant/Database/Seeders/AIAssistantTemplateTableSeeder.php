<?php

namespace Modules\AIAssistant\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Nwidart\Modules\Facades\Module;

class AIAssistantTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $modules = Module::all();

        if (Module::find('AIAssistant')?->isEnabled()) {
            foreach ($modules as $value) {
                if ($value->getName() != 'AIAssistant') {
                    $name = '\Modules\\' . $value->getName();
                    $path = $value->getPath();

                    if (file_exists($path . '/Database/Seeders/AIAssistantTemplateListTableSeeder.php')) {
                        $this->call($name . '\Database\Seeders\AIAssistantTemplateListTableSeeder');
                    }
                }
            }
        }
    }
}
