<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    /**
     * Create a new message instance.
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->view('Emails.EmailVerification')
        ->subject('XEPayroll Email Verification')
        ->with('verification_code', $this->code);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
