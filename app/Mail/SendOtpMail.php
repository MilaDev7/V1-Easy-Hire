<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class SendOtpMail extends Mailable
{
    public string $otp;
    public string $userName;

    public function __construct(string $otp, string $userName)
    {
        $this->otp = $otp;
        $this->userName = $userName;
    }

    public function build(): static
    {
        return $this->subject('Your Password Reset OTP')
                    ->view('emails.otp');
    }
}
