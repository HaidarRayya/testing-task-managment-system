<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

use Illuminate\Queue\SerializesModels;

class ErrorMessage extends Mailable
{
    use Queueable, SerializesModels;

    protected $errorMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }
    public function build()
    {
        return $this->subject('Error')
            ->view('Emails.error')
            ->with(['errorMessage' => $this->errorMessage]);
    }
}