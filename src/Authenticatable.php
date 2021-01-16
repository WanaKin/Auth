<?php
namespace WanaKin\Auth;

trait Authenticatable {
    /**
     * Define the password reset relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function passwordResetTokens() {
        return $this->morphMany( PasswordResetToken::class, 'authenticatable' );
    }
}
