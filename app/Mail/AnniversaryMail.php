<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnniversaryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $anniversary, public $wifeEmail)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Happy Anniversary!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $type = $this->anniversary['banner']['type'] === 'marriage' ? 'email.Marriage' : 'email.Relationship';
        $emailAddress = $this->to[0]['address'];
        $view = $emailAddress === $this->wifeEmail ? $type.'.romantic' : $type.'.announcement';
        
        return new Content(
            view: $view,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
