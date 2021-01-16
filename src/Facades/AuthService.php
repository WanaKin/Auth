<?php
namespace WanaKin\Auth\Facades;

use Illuminate\Support\Facades\Facade;

class AuthService extends Facade {
    /**
     * Get the service name
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return \WanaKin\Auth\AuthService::class;
    }
}
