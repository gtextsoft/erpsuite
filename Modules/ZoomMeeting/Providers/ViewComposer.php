<?php

namespace Modules\ZoomMeeting\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class ViewComposer extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function boot()
    {
        view()->composer(['recruitment::interviewSchedule.create'], function ($view) {
            if (Auth::check()) {
                $meeting = '';
                if (module_is_active('ZoomMeeting')) {
                    $meeting = 'ZoomMeeting';
                }
                $view->getFactory()->startPush('meeting_time', view('zoom-meeting::interviewSchedule.meeting_time', compact('meeting')));
            }
        });
    }

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
