<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReminderEmail extends Mailable implements Queueable
{
    use Queueable, SerializesModels;

    public $registration;

    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 Reminder: ' . $this->registration->event->event_title,
        );
    }

    public function content(): Content
    {
        // Ambil data event
        $event = $this->registration->event;
        
        // Generate Google Calendar URL
        $startTime = $event->date_start->format('Ymd') . 'T' . str_replace(':', '', $event->time_start) . '00';
        $endTime = $event->date_start->format('Ymd') . 'T' . str_replace(':', '', $event->time_end) . '00';
        
        $calendarUrl = "https://www.google.com/calendar/render?action=TEMPLATE" .
            "&text=" . urlencode($event->event_title) .
            "&dates=" . $startTime . "/" . $endTime .
            "&details=" . urlencode("Reminder webinar: " . $event->event_title . "\nKode Reg: " . $this->registration->registration_code) .
            "&location=" . urlencode(($event->link_zoom ?? 'Online via Zoom')) .
            "&sf=true&output=xml";

        return new Content(
            view: 'emails.reminder-event',
            with: [
                'reg' => $this->registration,
                'calendarUrl' => $calendarUrl,
            ]
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
