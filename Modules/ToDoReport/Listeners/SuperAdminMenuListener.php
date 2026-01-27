<?php

namespace Modules\ToDoReport\Listeners;
use App\Events\SuperAdminMenuEvent;

class SuperAdminMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(SuperAdminMenuEvent $event): void
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
