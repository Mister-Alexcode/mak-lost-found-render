<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ItemNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $type,
        public string $notificationMessage,
        public string $userName
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'match_found'         => 'A potential match has been found!',
            'claim_approved'      => 'Your claim has been approved!',
            'claim_rejected'      => 'Claim update',
            'new_message'         => 'You have a new message',
            'redemption_approved' => 'Your reward redemption is ready!',
        ];

        return new Envelope(
            subject: $subjects[$this->type] ?? 'MAK Lost & Found Notification',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
        );
    }
}
