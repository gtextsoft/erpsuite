<?php

namespace Modules\AIDocument\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'AIDocument';
        $menu = $event->menu;
        if (!in_array('AIImage', $event->menu->modules)) {
            $menu->add([
                'category' => 'AI',
                'title' => __('AI'),
                'icon' => 'brand-gitlab',
                'name' => 'ai',
                'parent' => null,
                'order' => 715,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => '',
                'module' => $module,
                'permission' => 'sidebar ai manage'
            ]);

            $menu->add([
                'category' => 'AI',
                'title' => __('History'),
                'icon' => '',
                'name' => 'ai-history',
                'parent' => 'ai',
                'order' => 20,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => '',
                'module' => $module,
                'permission' => null
            ]);
        }
        
        //  $menu->add([
        //     'category' => 'AI',
        //     'title' => __('AI Document'),
        //     'icon' => '',
        //     'name' => 'ai-document',
        //     'parent' => null,
        //     'order' => 300,
        //     'ignore_if' => [],
        //     'depend_on' => [],
        //     'route' => 'aidocument.index',
        //     'module' => $module,
        //     'permission' => 'project manage',
        // ]);

        $menu->add([
            'category' => 'AI',
            'title' => __('AI Document'),
            'icon' => 'brand-gitlab',
            'name' => 'ai-document',
            'parent' => '',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'aidocument.index',
            'module' => $module,
            'permission' => 'ai document manage'
        ]);

        $menu->add([
            'category' => 'AI',
            'title' => __('AI Document History'),
            'icon' => 'brand-gitlab',
            'name' => 'history-ai-document',
            'parent' => '',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'aidocument.document.history',
            'module' => $module,
            'permission' => 'document history manage'
        ]);
    }
}
