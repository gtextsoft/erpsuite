<?php

namespace Modules\QuizManagement\Events;

use Illuminate\Queue\SerializesModels;

class DestroyQuizParticipantes
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $quizParticipant;

    public function __construct($quizParticipant)
    {
        $this->quizParticipant  = $quizParticipant;
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
