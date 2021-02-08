<?php
namespace WanaKin\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Auth\Authenticatable as Model;
use WanaKin\Auth\Mail\PasswordReset;
use WanaKin\Auth\Mail\EmailAdded;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;

class AuthService {
    /**
     * Generate a random string
     *
     * @param int $size
     * @return string
     */
    private function random( int $size ) : string {
        return bin2hex( random_bytes( $size / 2 ) );
    }
    
    /**
     * Get the authenticatable model
     *
     * @return string
     */
    protected function getAuthenticatable() {
        return config( 'auth.providers.users.model', 'App\\Models\\User' );
    }

    /**
     * Register a user
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @return ?Model
     */
    public function register( string $name, string $email, string $password ) : ?Model {
        // Create the user
        $user = $this->getAuthenticatable()::create( [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make( $password )
        ] );

        // If the user was created
        if ( $user ) {
            // Fire the Registered event
            event( new Registered( $user ) );
            
            // Send the verification email
            $this->resend( $user );

            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Check if an email is available
     *
     * @param string $email
     * @param ?Model $user = NULL
     * @return bool
     */
    public function emailAvailable( string $email, ?Model $user = NULL ) : bool {
        // Check for the email in the users table
        $query = $this->getAuthenticatable()::where( 'email', $email );

        // If a user is provided, exempt them from the search
        if ( $user ) {
            $query->where( 'id', '!=', $user->id );
        }

        $usersTable = $query->doesntExist();

        // Check for the email in the pending table
        $query = EmailVerification::where( 'email', $email );

        if ( $user ) {
            $query->where( function ( $query ) use ( $user ) {
                    $query->where( 'verifiable_type', '!=', get_class( $user ) )
                          ->where( 'verifiable_id', '!=', $user->id );
                } );
        }

        $pendingTable = $query->doesntExist();

        return $usersTable && $pendingTable;
    }

    /**
     * (Re)send a verification email
     *
     * @param Model $user
     * @param string $email = ''
     * @return void
     */
    public function resend( Model $user, string $email = '' ) : void {
        // Fallback to the user's current email address
        $email = $email ?: $user->email;

        // Generate the verification URL
        $verification = $user->verifications()->create( [
            'email' => $email,
            'verification_slug' => $this->random( 32 )
        ] );
        $verificationUrl = route( 'auth.verify', [
            'emailVerification' => $verification
        ] );
        
        // Send the verification email
        Mail::to( (object)[
            'name' => $user->name,
            'email' => $email
        ] )->send( new EmailAdded( $verificationUrl ) );
    }

    /**
     * Verify a user's email address
     *
     * @param EmailVerification $emailVerification
     * @return bool
     */
    public function verify( EmailVerification $emailVerification ) : bool {
        // Make sure the email is new enough
        if ( $emailVerification->created_at->gte( now()->subHours( 2 ) ) ) {
            // Only update if the user's current verified timestamp is older than the current one
            if ( empty( $emailVerification->verifiable->email_verified_at ) || Carbon::parse( $emailVerification->verifiable->email_verified_at )->lt( $emailVerification->created_at ) ) {
                $emailVerification->verifiable()->update( [
                    'email' => $emailVerification->email,
                    'email_verified_at' => now()
                ] );

                // Delete the token
                $emailVerification->delete();

                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Attempt a login
     *
     * @param string $email
     * @param string $password
     * @param bool $remember = FALSE
     * @return ?Model
     */
    public function login( string $email, string $password, bool $remember = FALSE ) : ?Model {
        // Try to find the model
        if ( $user = $this->getAuthenticatable()::where( 'email', $email )->first() ) {
            // Make sure the passwords match
            if ( Hash::check( $password, $user->password ) ) {
                return $user;
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }

    /**
     * Update the name and email of a user
     *
     * @param Model $user
     * @param string $name
     * @param string $email
     * @return void
     */
    public function update( Model $user, string $name, string $email ) : void {
        // If the email has changed, send a verification email
        if ( $email !== $user->email ) {
            $this->resend( $user, $email );
        }

        // Update the name (the email will be updated when it's verified)
        $user->update( [
            'name' => $name,
        ] );
    }

    /**
     * Update the user's password
     *
     * @param Model|PasswordResetToken $user
     * @param string $password
     * @return bool
     */
    public function updatePassword( $user, string $password ) : bool {
        // If provided a password reset token, serialize the user and delete the token
        if ( $user instanceof PasswordResetToken ) {
            $passwordResetToken = $user;
            
            // Make sure the token is new enough
            if ( $passwordResetToken->created_at->gt( now()->subMinutes( config( 'auth.passwords.users.expire' ) ) ) ) {
                $user = $passwordResetToken->authenticatable;

                // Fire the password reset event
                event( new PasswordResetEvent( $user ) );

                // Delete the token
                $passwordResetToken->delete();
            } else {
                return FALSE;
            }
        }

        // Update the model
        return $user->update( [
            'password' => Hash::make( $password )
        ] );
    }

    /**
     * Send a password reset link
     *
     * @param string $email
     * @return void
     */
    public function sendPasswordResetLink( string $email ) : void {
        // Find the user
        if ( $user = $this->getAuthenticatable()::where( 'email', $email )->first() ) {
            // Create a new token
            $passwordResetToken = $user->passwordResetTokens()->create( [
                'token' => $this->random( 32 )
            ] );
            
            // Generate the full URL
            $passwordResetUrl = route( 'auth.password.reset', [
                'passwordResetToken' => $passwordResetToken
            ] );
            
            // Send the email
            Mail::to( $user )->send( new PasswordReset( $passwordResetUrl ) );
        }

        // We want to silently fail because we don't want to let potential attackers know which emails are associated with accounts
    }
}
