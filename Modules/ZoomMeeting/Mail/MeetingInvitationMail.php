<?php
namespace Modules\ZoomMeeting\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetingInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $meeting;

    public function __construct($meeting)
    {
        $this->meeting = $meeting;
    }

    public function build()
    {
        return $this->subject('Zoom Meeting Invitation')
                    ->view('zoommeeting::emails.meeting_invitation');
    }
}
