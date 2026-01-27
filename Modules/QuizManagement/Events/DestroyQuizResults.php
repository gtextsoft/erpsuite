<?php

namespace Modules\QuizManagement\Events;

use Illuminate\Queue\SerializesModels;

class DestroyQuizResults
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $quizResult;

    public function __construct($quizResult)
    {
        $this->quizResult   = $quizResult;
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
