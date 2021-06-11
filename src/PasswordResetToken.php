<?php
namespace WanaKin\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use WanaKin\Auth\Facades\AuthService;

class PasswordResetToken extends Model {
    /** @var string */
    protected $table = 'password_resets';

    /**
     * Properties that can't be mass assigned
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Disable updated_at column
     *
     * @return void
     */
    public function setUpdatedAtAttribute($value)
    {
        // Do nothing
    }

    /**
     * Get the user model
     *
     * @return ?Authenticatable
     */
    public function getAuthenticatableAttribute()
    {
        // Find the user by email
        return AuthService::getAuthenticatable()::where('email', $this->email)->first();

        //return $this->morphTo();
    }
}
