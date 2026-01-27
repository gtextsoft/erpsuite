<?php

namespace Modules\GoogleDrive\Providers;

use Modules\GoogleDrive\Entities\GoogleDriveSetting;
use Illuminate\Support\ServiceProvider;

class AddHookServiceProvider extends ServiceProvider
{

    public $views;

    public function boot(){


        $this->views = GoogleDriveSetting::get_view_to_stack_hook();  
        view()->composer(
            array_merge(...array_values($this->views)),
            function ($view) {
                $module = null;
                foreach ($this->views as $key => $views) {
                    if (in_array($view->getName(), $views)) {
                        $module = $key;
                        break;
                    }
                }
                if (\Auth::check() && $module) {
                    $active_module = ActivatedModule();
                    $dependency = explode(',', 'GoogleDrive');
                    
                    if (!empty(array_intersect($dependency, $active_module))) {
                        $view->getFactory()->startPush('addButtonHook', view('google-drive::layouts.addhook', compact('module')));
                    }
                }
            }
        );
        
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
