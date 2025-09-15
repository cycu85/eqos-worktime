<?php

namespace App\Mail;

use App\Models\Delegation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class DelegationPdfMail extends Mailable
{
    use SerializesModels;

    protected $delegation;
    protected $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Delegation $delegation, $pdfContent)
    {
        $this->delegation = $delegation;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $filename = 'delegacja_' . $this->delegation->id . '_' . 
                   str_replace(' ', '_', $this->delegation->full_name) . '.pdf';
                   
        return new Envelope(
            subject: 'Zaakceptowana delegacja - ' . $this->delegation->full_name . ' (' . $filename . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.delegation-pdf',
            with: [
                'delegation' => $this->delegation,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $filename = 'delegacja_' . $this->delegation->id . '_' . 
                   str_replace(' ', '_', $this->delegation->full_name) . '.pdf';
                   
        return [
            Attachment::fromData(fn () => $this->pdfContent, $filename)
                ->withMime('application/pdf'),
        ];
    }
}