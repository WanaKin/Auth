<?php
namespace WanaKin\Auth;

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
     * @return void
     */
    public function __construct( string $verificationUrl ) {
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->view( 'auth::email-added' );
    }
}
