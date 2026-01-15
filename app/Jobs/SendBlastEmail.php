<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class SendBlastEmail implements ShouldQueue
{
    use Batchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public $recipient, public $subject, public $content) {}

    public function middleware()
    {
        // Rate limit: 9 email per 120 detik (2 menit)
        return [(new \Illuminate\Queue\Middleware\RateLimited('blast-limiter'))];
    }

    public function handle()
    {
        if ($this->batch()->cancelled()) return;

        // 1. Ambil data nama dari recipient CSV
        $recipientName = $this->recipient->name ?? 'Unlockers';

        // 2. Ganti tag {name} yang ada di dalam konten editor dengan nama asli
        // Ini akan mengubah "Halo {name}" menjadi "Halo Budi" di dalam variabel $finalContent
        $finalContent = str_ireplace('{name}', $recipientName, $this->content);

        // 3. Kirim ke mailer
        Mail::send('emails.blast-layout', [
            'body' => $finalContent, // Konten yang sudah berisi nama asli
            'subject' => $this->subject
        ], function ($message) {
            $message->to($this->recipient->email)
                    ->subject($this->subject);
        });
    }

}
