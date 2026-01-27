<?php

namespace Modules\ToDoReport\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'ToDoReport';
        $menu = $event->menu;
        $menu->add([
            'title' => 'ToDoReport',
            'icon' => 'home',
            'name' => 'todoreport',
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
