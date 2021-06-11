<?php
namespace WanaKin\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailAdded extends Mailable {
    use Queueable, SerializesModels;

    /** @var string */
    public $verificationUrl;

    /**
     * Create a new message instance.
     *
     * @param  string $verificationUrl The email verification URL
     * @return void
     */
    public function __construct($verificationUrl) {
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->view('auth::email-added');
    }
}
