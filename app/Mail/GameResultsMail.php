<?php

namespace App\Mail;

use App\Models\GameSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GameResultsMail extends Mailable
{
    use Queueable, SerializesModels;

    public GameSession $session;

    /**
     * Create a new message instance.
     */
    public function __construct(GameSession $session)
    {
        $this->session = $session;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Playard Curling Results',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.game-results',
            with: [
                'session' => $this->session,
                'shareUrl' => route('share.show', $this->session->share_code),
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
        return [];
    }
}