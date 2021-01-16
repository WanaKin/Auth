<?php
namespace WanaKin\Auth;

trait Verifiable {
    /**
     * Get the verfications for the model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function verifications() {
        return $this->morphMany( EmailVerification::class, 'verifiable' );
    }

    /**
     * Checks if the user is verified
     *
     * @return bool
     */
    public function getEmailVerifiedAttribute() {
        return !empty( $this->email_verified_at );
    }
}
