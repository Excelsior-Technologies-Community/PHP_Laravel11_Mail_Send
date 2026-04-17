<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Ensure MAIL_FROM_ADDRESS is set in your .env
        $mail = $this->from(config('mail.from.address'), config('mail.from.name'))
                     ->subject($this->details['title'])
                     ->view('emails.test')
                     ->with('details', $this->details);

        // Attach multiple files if available
        if (isset($this->details['attachments']) && is_array($this->details['attachments'])) {
            foreach ($this->details['attachments'] as $filePath) {
                $fullPath = storage_path('app/public/' . $filePath);
                
                if (file_exists($fullPath)) {
                    $mail->attach($fullPath);
                }
            }
        }

        return $mail;
    }
}