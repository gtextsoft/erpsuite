<?php

namespace Modules\ZoomMeeting\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanyMenuEvent;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use Modules\Appointment\Events\AppointmentCallbackEvent;
use Modules\Appointment\Events\AppointmentStatus;
use Modules\Recruitment\Events\CreateInterviewSchedule;
use Modules\ZoomMeeting\Listeners\AppointmentCallbackLis;
use Modules\ZoomMeeting\Listeners\CompanyMenuListener;
use Modules\ZoomMeeting\Listeners\CompanySettingListener;
use Modules\ZoomMeeting\Listeners\CompanySettingMenuListener;
use Modules\ZoomMeeting\Listeners\CreateAppointmentLis;
use Modules\ZoomMeeting\Listeners\CreateInterviewScheduleLis;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class,
        ],
        CompanySettingEvent::class => [
            CompanySettingListener::class,
        ],
        CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class,
        ],
        AppointmentCallbackEvent::class => [
            AppointmentCallbackLis::class,
        ],
        AppointmentStatus::class => [
            CreateAppointmentLis::class,
        ],
        CreateInterviewSchedule::class => [
            CreateInterviewScheduleLis::class,
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
}
