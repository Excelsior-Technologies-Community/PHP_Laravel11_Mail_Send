<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TestMail extends Mailable
{
    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

  public function build()
    {
        $mail = $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                     ->subject($this->details['title'])
                     ->view('emails.test')
                     ->with('details', $this->details); // ✅ VERY IMPORTANT

        // 📎 Attachment
        if (isset($this->details['attachment'])) {
            $mail->attach(storage_path('app/public/' . $this->details['attachment']));
        }

        return $mail;
    }
}
