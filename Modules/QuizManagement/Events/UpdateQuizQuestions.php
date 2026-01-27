<?php

namespace Modules\QuizManagement\Events;

use Illuminate\Queue\SerializesModels;

class UpdateQuizQuestions
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $quizQuestion;

    public function __construct($request, $quizQuestion)
    {
        $this->request          = $request;
        $this->quizQuestion     = $quizQuestion;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
