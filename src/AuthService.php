<?php
namespace WanaKin\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Auth\Authenticatable as Model;
use WanaKin\Auth\Mail\PasswordReset;
use WanaKin\Auth\Mail\EmailAdded;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;

/**
 * Handles various authentication tasks. This class can be used via dependency injection or with the facade at WanaKin\Auth\Facades\AuthService.
 */
class AuthService {
    /**
     * Generate a random string
     *
     * @param  int $size The size of the string to generate.
     * @return string
     */
    protected function random($size)
    {
        return bin2hex(random_bytes($size / 2));
    }

    /**
     * Get the authenticatable model
     *
     * @return string
     */
    public function getAuthenticatable()
    {
        return config('auth.providers.users.model', 'App\\Models\\User');
    }

    /**
     * Register a user
     *
     * @param  string $name The registering user's name
     * @param  string $email The registering user's email
     * @param  string $password The registering user's plaintext password
     * @param  array $defaults An array to set any additional default values. This can also be used to set custom options (e.g. a user's role).
     * @return ?Model The model of the newly created user on success, null on failure
     */
    public function register($name, $email, $password, $defaults = [])
    {
        // Merge the credentials
        $creds = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password)
        ];

        $creds = array_merge($creds, $defaults);

        // Create the user
        $user = $this->getAuthenticatable()::create($creds);

        // If the user was created
        if ($user) {
            // Fire the Registered event
            event(new Registered($user));

            // Send the verification email
            $this->resend($user);

            return $user;
        } else {
            return null;
        }
    }

    /**
     * Check if an email is available
     *
     * @param  string $email The email to check
     * @param  ?Model $user Pass a user to exclude them from the availability query
     * @return bool
     */
    public function emailAvailable($email, $user = null)
    {
        // Check for the email in the users table
        $query = $this->getAuthenticatable()::where('email', $email);

        // If a user is provided, exempt them from the search
        if ($user) {
            $query->where('id', '!=', $user->id);
        }

        $usersTable = $query->doesntExist();

        // Check for the email in the pending table
        $query = EmailVerification::where('email', $email);

        // If a user is provided, exclude their ID from the search
        if ($user) {
            $query->where(function ($query) use ($user) {
                    $query->where('verifiable_type', '!=', get_class($user))
                          ->where('verifiable_id', '!=', $user->id);
                });
        }

        $pendingTable = $query->doesntExist();

        // If both don't exist, the email is available
        return $usersTable && $pendingTable;
    }

    /**
     * (Re)send a verification email
     *
     * @param  Model $user The user to send the verification email to
     * @param  string $email Set a custom email to send to an address other than the one on record
     * @return void
     */
    public function sendVerificationEmail($user, $email = '')
    {
        // Fallback to the user's current email address if none provided
        $email = $email ?: $user->email;

        // Generate the verification URL
        $verification = $user->verifications()->create([
            'email' => $email,
            'verification_slug' => $this->random(32)
        ]);
        $verificationUrl = route('auth.verify', $verification);

        // Send the verification email
        Mail::to((object)[
            'name' => $user->name,
            'email' => $email
        ])->send(new EmailAdded($verificationUrl));
    }

    /**
     * Alias of sendVerificationEmail
     *
     * @param  Model $user
     * @param  string $email
     * @return void
     */
    public function resend($user, $email = '')
    {
        return $this->sendVerificationEmail($user, $email);
    }

    /**
     * Verify a user's email address
     *
     * @param  EmailVerification $emailVerification The email verification token to check
     * @return bool
     */
    public function verify($emailVerification) {
        // Make sure the email is new enough
        if ($emailVerification->created_at->gte(now()->subHours(2))) {
            // Only update if the user's current verified timestamp is older than the current one
            if (empty($emailVerification->verifiable->email_verified_at) || Carbon::parse($emailVerification->verifiable->email_verified_at)->lt($emailVerification->created_at)) {
                // Update the email and verification timestamp
                $emailVerification->verifiable()->update([
                    'email' => $emailVerification->email,
                    'email_verified_at' => now()
                ]);

                // Delete the token
                $emailVerification->delete();

                // The verification succeeded
                return true;
            }
        }

        // The verification failed
        return false;
    }

    /**
     * Attempt a login
     *
     * @param  string $email The authenticating user's email
     * @param  string $password The authenticating user's password
     * @return ?Model The model to sign in on success, or null on failure
     */
    public function login($email, $password)
    {
        // Try to find the model
        if ($user = $this->getAuthenticatable()::where('email', $email)->first()) {
            // Make sure the passwords match
            if (Hash::check($password, $user->password)) {
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
     * @param  Model $user
     * @param  string $name
     * @param  string $email
     * @return void
     */
    public function update($user, $name, $email)
    {
        // If the email has changed, send a verification email
        if ($email !== $user->email) {
            $this->resend($user, $email);
        }

        // Update the name (the email will be updated when it's verified)
        $user->update([
            'name' => $name,
        ]);
    }

    /**
     * Update the user's password
     *
     * @param  Model|PasswordResetToken $user The user or password reset token model
     * @param  string $password The new plaintext password
     * @return bool
     */
    public function updatePassword($user, $password)
    {
        // If provided a password reset token, serialize the user and delete the token
        if ($user instanceof PasswordResetToken) {
            $passwordResetToken = $user;

            // Make sure the token is new enough
            if ($passwordResetToken->created_at->gt(now()->subMinutes(config('auth.passwords.users.expire')))) {
                $user = $passwordResetToken->authenticatable;

                // Fire the password reset event
                event(new PasswordResetEvent($user));

                // Delete the token
                $passwordResetToken->delete();
            } else {
                return FALSE;
            }
        }

        // Update the model
        return $user->update([
            'password' => Hash::make($password)
        ]);
    }

    /**
     * Send a password reset link
     *
     * @param  string $email The email to send the URL to
     * @return void
     */
    public function sendPasswordResetLink($email)
    {
        // Find the user
        if ($user = $this->getAuthenticatable()::where('email', $email)->first()) {
            // Create a new token
            $passwordResetToken = PasswordResetToken::create([
                'email' => $email,
                'token' => $this->random(32)
            ]);

            // Generate the full URL
            $passwordResetUrl = route('auth.password.reset', [
                'passwordResetToken' => $passwordResetToken
            ]);

            // Send the email
            Mail::to($user)->send(new PasswordReset($passwordResetUrl));
        }

        // We want to silently fail because we don't want to let potential attackers know which emails are associated with accounts
    }
}
