<?php

namespace App\Mail\Admin;

use App\Models\RequestQuote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewRFQ extends Mailable
{
    use Queueable, SerializesModels;

    public $rfq;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(RequestQuote $rfq)
    {
        $this->rfq = $rfq;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.admin.rfq')
        ->subject('New Request for Quote');
    }
}
