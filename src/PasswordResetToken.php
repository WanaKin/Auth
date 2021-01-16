<?php
namespace WanaKin\Auth;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model {
    /**
     * Properties that can't be mass assigned
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the user model
     */
    public function authenticatable() {
        return $this->morphTo();
    }
}
