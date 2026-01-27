<?php

namespace Modules\QuizManagement\Events;

use Illuminate\Queue\SerializesModels;

class CreateQuizResults
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $quizResult;

    public function __construct($request, $quizResult)
    {
        $this->request      = $request;
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
