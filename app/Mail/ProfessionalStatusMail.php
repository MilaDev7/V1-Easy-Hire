<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;

class ProfessionalStatusMail extends Mailable
{
    public $messageText;

    public function __construct($messageText)
    {
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject('Account Status Update')
                    ->view('emails.professional_status');
    }
}