<?php
namespace WanaKin\Auth\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * See WanaKin\Auth\AuthService for full method documentation.
 *
 * @method static string random(int $size)
 * @method static ?Authenticatable register(string $name, string $email, string $password, array $defaults = [])
 * @method static bool emailAvailable(string $email, ?Model $user = NULL)
 * @method static void resend(Model $user, string $email = '')
 * @method static bool verify(EmailVerification $emailVerification)
 * @method static ?Authenticatable login(string $email, string $password, bool $remember = FALSE)
 * @method static void update(Model $user, string $name, string $email)
 * @method static bool updatePassword($user, string $password)
 * @method static void sendPasswordResetLink(string $email)
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
