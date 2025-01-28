<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $path;
    public $data;

    public function __construct($path, $data)
    {
        $this->path = $path;
        $this->data = $data;
    }

    public function build()
    {
        return $this->view('Emails.payslip')
        ->subject('Your Payslip for '. $this->data['paydate'])
        ->attach(storage_path('app/' . $this->path), [
            'as' => 'payslip.pdf',
            'mime' => 'application/pdf',
        ])
        ->with(['data' => $this->data]);
    }
}