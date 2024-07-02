<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    public function __construct($reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        return $this->subject('Reservation ' . $this->reservation->status)
                    ->view('emails.reservation_status')
                    ->with([
                        'customer' => $this->reservation->customer,
                        'status' => $this->reservation->status,
                        'date' => $this->reservation->date,
                        'time_slot' => $this->reservation->time_slot,
                    ]);
    }
}
