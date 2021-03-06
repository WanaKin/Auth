# Auth
A simple authentication package for Laravel that uses the Bootstrap CSS framework (no JS!).

## Why?
This package was created because we wanted a simple, hands-off authentication system for Laravel and couldn't find anything that suited our needs. This package uses Bootstrap CSS framework from the official CDN (so you don't need to make any changes to your application's webpack config) and makes **zero** changes to your application's files. Everything is handled completely from the package (but can still be customized!), and uses Laravel's own Auth facade and User model so you can switch away at any time without losing any data. No set up is required from you, besides the package installation and adding two lines to your User model.

This package currently does the following for you out of the box:

* Registration
* Login
* Email verification
* Password resets
* Simple dashboard to update username, email, and password

## Installation
In order to install this package, simply add it to composer:

```bash
composer require wanakin/auth
```

All of the routes, migrations, and views will then automatically be registered by Laravel. You'll also need to add the `WanaKinAuth` trait to your User model:

```
use WanaKin\Auth\WanaKinAuth;
...
class User {
	use WanaKinAuth;
	...
}
```

## Configuration
### Custom Validation Rules
You can define custom validation rules for both registration and updates in your `config/auth.php` file. The validation rules will be merged with the package's default rules, so you only need to override the fields you'd like to change. For example, to require a unique name on registration, you'd use something like this:

```php
'validation' => [
	'register' => [
		'name' => 'required|unique:users,name'
	]
],
```

#### Unique Ignore
If you'd like to have unique update rules, you'll need to ignore the user's current values otherwise they won't be able to save anything. In order to fix this, pass `{userId}` as the third argument to the `unique` rule:

```php
'validation' => [
	'update' => [
		'name' => 'required|unique:users,name,{userId}'
	],
],
```

This will be replaced with the current user's actual ID before merging the rules.

### Password Reset Link Expiration
This package respects the expiration time set in `config/auth.php`.

### Redirects
You can customize where the user will be redirected by adding a `redirects` array to your `config/auth.php` file. Here are the currently available options:

```php
'redirect' => [
	// When a user logs in
	'login' => '/',
	// When a user registers for an account
	'register' => '/',
	// When a user verifies their email address
	'verify' => '/'
],
```

Alternatively, you can set a global redirect for any of the actions by providing a string instead of an array:

```php
'redirect' => '/'
```

## Routes
This package will add the following routes to your Laravel application:

|Verb|Name|Route|
|---|-----|-----|
|GET|auth.register|/register|
|POST||/register|
|GET|login|/login|
|POST||/login|
|GET|auth.logout|/logout|
|GET|dashboard.auth|/dashboard/auth|
|POST||/dashboard/auth|
|POST|auth.dashboard.password|/dashboard/auth/password
|GET|auth.password.forgot|/forgot-password|
|POST||/forgot-password|
|GET|auth.password.reset|/reset-password/{passwordResetToken:token}|
|POST||/reset-password/{passwordResetToken:token}|
|GET|auth.dashboard.resend|/dashboard/auth/resend

## Checking if a user verified their email
Once you've added the `Verifiable` trait (included in the `WanaKinAuth` trait) to your User model, you can use the `emailVerified` attribute to check if the user has verified their email:

```php
if ( $user->emailVerified ) {
...
}
```

### Middleware
You can also use the `EnsureEmailIsVerified` middleware. To use this in routes, modify your `app/Http/Kernel.php`, and locate the `routeMiddleware` array. Then, simply replace the current entry for 'verified' with:

```php
'verified' => \WanaKin\Auth\Middleware\EnsureEmailIsVerified::class,
```

## Migrations
This package will register two migrations for the following tables:

* `email_verifications`
* `password_reset_tokens`

Simply run `php artisan migrate` after installing this package for the tables to be created.

## Events
Currently, the following events are dispatched (in the `Illuminate\Auth\Events` namespace unless otherwise stated):
* `Registered` on user registration
* `Login` on user login
* `Logout` on user logout
* `PasswordReset` when a user successfully resets their password

## Customizing Views
This package sets up some default views using the Bootstrap CSS framework. In order to customize the views while still utilizing the controller, you can create the corresponding views in `resources/views/vendor/auth/`. You can look at the `resources/views` folder of this package to see the views you can override. For example, to override the packages `login` view, you can create the file `resources/views/vendor/auth/login.blade.php` in your Laravel application and your view will be displayed instead of the default.

## Creating your own controller
In order to make this package as flexible as possible, most of the functionality is implemented in the `WanaKin\Auth\AuthService` class. More thorough documentation on this will be added soon, but in the meantime you can look at the `src/AuthService.php` class to see the available methods. If you'd prefer a facade, you can use `WanaKin\Auth\Facades\AuthService` instead. You'll also need to add your own routes for the new controller. The ability to specify a custom controller for the default routes is in the works.

Alternatively, you can choose to extend the default controller `WanaKin\Auth\AuthController` and only change the methods that deviate from the package's built-in functionality.

## API Routes
If you'd like to use API authentication, then add the following to your `config/auth.php`:

```php
'routes' => [
	'api' => TRUE
]
```

You can also disable the web routes:

```php
'routes' => [
	'api' => TRUE,
	'web' => FALSE
]
```

And be sure to install `laravel/sanctum` via composer. Currently, two routes are set via the API:

|Verb|Name|Route|
|---|-----|-----|
|POST|auth.register|/api/register|
|POST|auth.login|/api/login|

In order to log in, send the `email` and `password` via JSON. Upon a successfull login, you'll receive a response that looks like this:

```json
{
	"message": "Log in successful",
	"token": "abc123"
}
```

Be sure to store the token and send it in the `Authorization` header on subsequent requests.

Registration works similiarly; send a payload with `email`, `name`, and `'password`. Upon a successfull registration, you'll receive a response similar to:

```json
{
	"message": "Registration successful",
	"token": "abc123"
}
```

In both cases, any invalid parameters will result in a 422 error explaining which fields are incorrect and why.
