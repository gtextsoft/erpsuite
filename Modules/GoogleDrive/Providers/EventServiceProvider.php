<?php

namespace Modules\GoogleDrive\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use Modules\GoogleDrive\Listeners\CompanySettingListener;
use Modules\GoogleDrive\Listeners\CompanySettingMenuListener;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CompanySettingEvent::class => [
            CompanySettingListener::class,
        ],
        CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class,
        ],
    ];

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
    
//     public function boot()
// {
//     parent::boot();
// }

}
