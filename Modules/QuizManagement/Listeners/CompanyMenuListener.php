<?php

namespace Modules\QuizManagement\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'QuizManagement';
        $menu = $event->menu;
        $menu->add([
            'category' => 'General',
            'title' => __('Quiz Dashboard'),
            'icon' => 'trash',
            'name' => 'quiz-dashboard',
            'parent' => 'dashboard',
            'order' => 345,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'dashboard.quiz',
            'module' => $module,
            'permission' => 'quiz dashboard manage'
        ]);

        $menu->add([
            'category' => 'Productivity',
            'title' => __('Quiz Management'),
            'icon' => 'puzzle',
            'name' => 'quizmanagement',
            'parent' => null,
            'order' => 1363,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'quiz manage'
        ]);

        $menu->add([
            'category' => 'Productivity',
            'title' => __('Quizzes'),
            'icon' => 'trash',
            'name' => 'quizzes',
            'parent' => 'quizmanagement',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'quizzes.index',
            'module' => $module,
            'permission' => 'quizzes manage'
        ]);

        $menu->add([
            'category' => 'Productivity',
            'title' => __('Quiz Questions'),
            'icon' => 'trash',
            'name' => 'quiz-questions',
            'parent' => 'quizmanagement',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'quiz.questions.index',
            'module' => $module,
            'permission' => 'quizquestions manage'
        ]);

        $menu->add([
            'category' => 'Productivity',
            'title' => __('Quiz Participants'),
            'icon' => 'trash',
            'name' => 'quiz-participants',
            'parent' => 'quizmanagement',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'quiz.participate.index',
            'module' => $module,
            'permission' => 'quizparticipants manage'
        ]);

        $menu->add([
            'category' => 'Productivity',
            'title' => __('Quiz Results'),
            'icon' => 'trash',
            'name' => 'quiz-results',
            'parent' => 'quizmanagement',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'quiz.results.index',
            'module' => $module,
            'permission' => 'quizresults manage'
        ]);

        $menu->add([
            'category' => 'Productivity',
            'title' => __('Quiz Setup'),
            'icon' => 'trash',
            'name' => 'quiz-setup',
            'parent' => 'quizmanagement',
            'order' => 50,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'quiz.categories.index',
            'module' => $module,
            'permission' => 'quizcategories manage'
        ]);
    }
}
