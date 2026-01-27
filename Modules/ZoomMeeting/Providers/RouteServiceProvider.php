<?php
        
namespace Modules\ZoomMeeting\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
        * The module namespace to assume when generating URLs to actions.
        *
        * @var string
        */
    protected $moduleNamespace = 'Modules\ZoomMeeting\Http\Controllers';

    /**
        * Called before routes are registered.
        *
        * Register any model bindings or pattern based filters.
        *
        * @return void
        */
   public function boot()
{
    $this->app->register(\Modules\ZoomMeeting\Providers\RouteServiceProvider::class);
}


    /**
        * Define the routes for the application.
        *
        * @return void
        */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
        * Define the "web" routes for the application.
        *
        * These routes all receive session state, CSRF protection, etc.
        *
        * @return void
    //     */
    // protected function mapWebRoutes()
    // {
    //      Route::middleware('web')
    //         ->namespace($this->moduleNamespace)
    //         ->group(module_path('ZoomMeeting', '/Routes/web.php'));
    // }
    
    protected function mapWebRoutes()
{
    Route::middleware('web')
        ->namespace($this->moduleNamespace)
        ->group(base_path('Modules/ZoomMeeting/Routes/web.php')); // Use base_path
}

protected function mapApiRoutes()
{
    Route::prefix('api')
        ->middleware('api')
        ->namespace($this->moduleNamespace)
        ->group(base_path('Modules/ZoomMeeting/Routes/api.php')); // Use base_path
}


    // /**
    //     * Define the "api" routes for the application.
    //     *
    //     * These routes are typically stateless.
    //     *
    //     * @return void
    //     */
    // protected function mapApiRoutes()
    // {
    //      Route::prefix('api')
    //         ->middleware('api')
    //         ->namespace($this->moduleNamespace)
    //         ->group(module_path('ZoomMeeting', '/Routes/api.php'));
    // }
}