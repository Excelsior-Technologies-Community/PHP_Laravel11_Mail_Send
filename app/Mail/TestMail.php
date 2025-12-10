<?php

namespace App\Mail;

use Illuminate\Mail\Mailable; // Import the Mailable class from Laravel

class TestMail extends Mailable
{
    public $details; // Public property to hold email details, accessible in the view

    /**
     * Constructor to initialize email details
     *
     * @param array $details - Contains information like 'title', 'body', etc.
     */
    public function __construct($details)
    {
        $this->details = $details; // Store the details in the class property
    }

    /**
     * Build the email message.
     *
     * @return $this
     */
    public function build()
    {
        // Set the subject of the email using the 'title' from details
        // Load the Blade view 'emails.test' for the email content
        return $this->subject($this->details['title'])
                    ->view('emails.test');
    }
}
