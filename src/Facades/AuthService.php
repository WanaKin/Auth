<?php
namespace WanaKin\Auth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * See WanaKin\Auth\AuthService for full method documentation.
 *
 * @method private function random(int $size) : string
 * @method protected function getAuthenticatable()
 * @method public function register(string $name, string $email, string $password) : ?Model
 * @method public function emailAvailable(string $email, ?Model $user = NULL) : bool
 * @method public function resend(Model $user, string $email = '') : void
 * @method public function verify(EmailVerification $emailVerification) : bool {
 * @method public function login(string $email, string $password, bool $remember = FALSE) : ?Model
 * @method public function update(Model $user, string $name, string $email) : void
 * @method public function updatePassword($user, string $password) : bool
 * @method public function sendPasswordResetLink(string $email) : void
 *
 */
class AuthService extends Facade {
    /**
     * Get the service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \WanaKin\Auth\AuthService::class;
    }
}
