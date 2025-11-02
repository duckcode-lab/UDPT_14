<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminCancelBookingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $movieTitle, $showTime, $seats;

    public function __construct($user, $movieTitle, $showTime, $seats)
    {
        $this->user = $user;
        $this->movieTitle = $movieTitle;
        $this->showTime = $showTime;
        $this->seats = $seats;
    }

    public function build()
    {
        return $this->subject('🎟️ Thông báo huỷ vé - Cinema Ticket')
            ->view('emails.admin_cancel_booking');
    }
}
