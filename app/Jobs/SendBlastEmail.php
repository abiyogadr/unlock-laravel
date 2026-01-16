<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBlastEmail implements ShouldQueue
{
    public $timeout = 28800;
    use Batchable, InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    // Tambahkan $batchId ke dalam constructor
    public function __construct(
        public $recipient, 
        public $subject, 
        public $content,
        public $batchId // Parameter baru
    ) {}

    public function retryUntil()
    {
        return now()->addHours(20);
    }

    public function middleware()
    {
        return [(new \Illuminate\Queue\Middleware\RateLimited('blast-limiter'))];
    }

    public function handle()
    {   
        set_time_limit(28800);
        if ($this->batch()->cancelled()) return;

        $recipientName = $this->recipient->name ?? 'Unlockers';

        // 1. Generate Tracking URL
        $trackingUrl = route('tools.email-blast.track', [
            'b' => $this->batchId,
            'e' => base64_encode($this->recipient->email)
        ]);

        // 2. Ganti tag {name} di konten
        $finalContent = str_ireplace('{name}', $recipientName, $this->content);

        // 3. Kirim ke mailer dengan menyertakan $trackingUrl
        Mail::send('emails.blast-layout', [
            'body' => $finalContent,
            'subject' => $this->subject,
            'trackingUrl' => $trackingUrl // Teruskan ke layout blade
        ], function ($message) {
            $message->to($this->recipient->email)
                    ->subject($this->subject);
        });
    }
}
