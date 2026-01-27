<?php

namespace Modules\QuizManagement\Events;

use Illuminate\Queue\SerializesModels;

class DestroyQuizQuestions
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $quizQuestion;

    public function __construct($quizQuestion)
    {
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
