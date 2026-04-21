<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Sesuaikan alamat pengirim dengan nilai di .env (MAIL_FROM_ADDRESS)
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Test Sender'))
                    ->subject('Test Kirim Email Dari Laravel')
                    ->view('emails.test')          // blade view yang akan kita buat
                    ->with(['data' => $this->data]);
    }
}