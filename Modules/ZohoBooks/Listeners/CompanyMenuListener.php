<?php

namespace Modules\ZohoBooks\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'ZohoBooks';
        $menu = $event->menu;
        $menu->add([
            'title' => 'ZohoBooks',
            'icon' => 'home',
            'name' => 'zohobooks',
            'parent' => null,
            'order' => 2,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'home',
            'module' => $module,
            'permission' => 'manage-dashboard'
        ]);
    }
}
