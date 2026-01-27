<?php

namespace Modules\QuizManagement\Events;

use Illuminate\Queue\SerializesModels;

class DestroyQuizzes
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $quiz;

    public function __construct($quiz)
    {
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
