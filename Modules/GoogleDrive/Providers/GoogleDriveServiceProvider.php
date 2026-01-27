<?php

namespace Modules\GoogleDrive\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\GoogleDrive\Providers\EventServiceProvider;
use Modules\GoogleDrive\Providers\RouteServiceProvider;
use Modules\GoogleDrive\Providers\AddHookServiceProvider;

class GoogleDriveServiceProvider extends ServiceProvider
{

    protected $moduleName = 'GoogleDrive';
    protected $moduleNameLower = 'googledrive';

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(AddHookServiceProvider::class);
    }

    public function boot()
    {
        //  \Log::info('GoogleDriveServiceProvider boot method called');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'google-drive');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerTranslations();
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(__DIR__.'/../Resources/lang');
        }
    }
}