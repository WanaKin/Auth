<?php
namespace WanaKin\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordReset extends Mailable {
    use Queueable, SerializesModels;
    
    /** @var string */
    public $passwordResetUrl;

    /**
     * Create a new message instance.
     *
     * @param string $passwordResetUrl
     * @return void
     */
    public function __construct( string $passwordResetUrl ) {
        $this->passwordResetUrl = $passwordResetUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->view( 'auth::password-reset' );
    }
}
