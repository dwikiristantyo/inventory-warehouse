<?php

namespace App\Mail;

use App\Models\ItemRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ItemRequestStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ItemRequest $itemRequest,
        public string $event,
    ) {
        $this->itemRequest->loadMissing(['warehouse', 'details.item']);
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->event) {
            'submitted' => "Permintaan barang baru: {$this->itemRequest->request_no}",
            'accepted' => "Permintaan barang diterima: {$this->itemRequest->request_no}",
            default => "Permintaan barang ditolak: {$this->itemRequest->request_no}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.item-request-status',
            with: ['event' => $this->event],
        );
    }
}
