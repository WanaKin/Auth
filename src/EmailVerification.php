<?php
namespace WanaKin\Auth;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model {
    /**
     * Properties that can't be mass assigned
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the user model
     */
    public function verifiable() {
        return $this->morphTo();
    }
}
