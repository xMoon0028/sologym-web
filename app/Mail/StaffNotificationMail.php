<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $notificationTitle;
    public string $messageBody;
    public ?string $actionUrl;
    public ?string $actionText;
    public string $recipientName;
    public array $customAttachments;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $title,
        string $messageBody,
        ?string $actionUrl = null,
        ?string $actionText = null,
        string $recipientName = 'Staff Member',
        array $customAttachments = []
    ) {
        $this->notificationTitle = $title;
        $this->messageBody = $messageBody;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
        $this->recipientName = $recipientName;
        $this->customAttachments = $customAttachments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Strip out leading/trailing emoji glyphs for a crisp, professional subject header
        $cleanSubject = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F900}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $this->notificationTitle);
        $cleanSubject = trim($cleanSubject) ?: $this->notificationTitle;

        return new Envelope(
            subject: "[LASAC HRIS] {$cleanSubject}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'title' => $this->notificationTitle,
                'messageBody' => $this->messageBody,
                'actionUrl' => $this->actionUrl,
                'actionText' => $this->actionText,
                'recipientName' => $this->recipientName,
                'hasAttachment' => !empty($this->customAttachments),
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
        $attachments = [];

        foreach ($this->customAttachments as $att) {
            if (isset($att['data'], $att['name'])) {
                $attachments[] = Attachment::fromData(
                    fn () => $att['data'],
                    $att['name']
                )->withMime($att['options']['mime'] ?? 'application/pdf');
            } elseif (isset($att['path'])) {
                $attachment = Attachment::fromPath($att['path']);
                if (isset($att['name'])) {
                    $attachment->as($att['name']);
                }
                if (isset($att['options']['mime'])) {
                    $attachment->withMime($att['options']['mime']);
                }
                $attachments[] = $attachment;
            }
        }

        return $attachments;
    }
}
