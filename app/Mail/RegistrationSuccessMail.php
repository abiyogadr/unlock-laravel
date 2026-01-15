<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationSuccessMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $registration;

    /**
     * Create a new message instance.
     */
    public function __construct($registration)
    {
        $this->registration = $registration;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Berhasil - ' . $this->registration->registration_code,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Ambil data event dari pendaftaran
        $event = $this->registration->event; // Pastikan relasi 'event' sudah ada di model Registration
        
        // Format data untuk link Google Calendar
        $startTime = $event->date_start->format('Ymd') . 'T' . str_replace(':', '', $event->time_start) . '00';
        $endTime = $event->date_start->format('Ymd') . 'T' . str_replace(':', '', $event->time_end) . '00';
        
        $calendarUrl = "https://www.google.com/calendar/render?action=TEMPLATE" .
            "&text=" . urlencode($event->event_title) .
            "&dates=" . $startTime . "/" . $endTime .
            "&details=" . urlencode("Pengingat untuk webinar: " . $event->event_title) .
            "&location=" . urlencode("Online via Zoom/Google Meet") .
            "&sf=true&output=xml";

        return new Content(
            view: 'emails.registration-success',
            with: [
                'calendarUrl' => $calendarUrl,
                'event' => $event
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
