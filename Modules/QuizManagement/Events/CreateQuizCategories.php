<?php

namespace Modules\QuizManagement\Events;

use Illuminate\Queue\SerializesModels;

class CreateQuizCategories
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $quizCategorie;

    public function __construct($request, $quizCategorie)
    {
        $this->request              = $request;
        $this->quizCategorie     = $quizCategorie;
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
