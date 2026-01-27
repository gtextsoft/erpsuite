<?php

namespace Modules\GoogleDrive\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingMenuEvent $event): void
    {
         \Log::info('GoogleDrive Menu triggered');
        $module = 'GoogleDrive';
        $menu = $event->menu;
        $menu->add([
            'title' => __('Google Drive Settings'),
            'name' => 'googledrive',
            'order' => 670,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'google-drive',
            'module' => $module,
            'permission' => 'googledrive manage'
        ]);
    }
}
