<?php
namespace Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Model;
use WanaKin\Auth\WanaKinAuth;
use Laravel\Sanctum\HasApiTokens;

class User extends Model {
    use WanaKinAuth, HasApiTokens;
    
    /**
     * Properties that can't be mass assigned
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
