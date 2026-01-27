<?php

namespace Modules\QuizManagement\Events;

use Illuminate\Queue\SerializesModels;

class CreateQuizParticipantes
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $quizParticipants;

    public function __construct($request, $quizParticipants)
    {
        $this->request              = $request;
        $this->quizParticipants     = $quizParticipants;
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
