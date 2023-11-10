<?php

namespace App\Mail\Admin;

use App\Models\Frontend\Careers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Storage;

class NewCareer extends Mailable
{
    use Queueable, SerializesModels;

    public $career;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Careers $career)
    {
        $this->career = $career;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->attach(Storage::path('storage/' . $this->career->resume), [
                'mime' => 'application/pdf',
            ])
            ->view('emails.admin.career')
            ->subject("New Career Application from website");
    }
}
