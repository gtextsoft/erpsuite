<?php

namespace Modules\QuizManagement\Events;

use Illuminate\Queue\SerializesModels;

class CreateQuizzes
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $quiz;

    public function __construct($request, $quiz)
    {
        $this->request  = $request;
        $this->quiz     = $quiz;
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
