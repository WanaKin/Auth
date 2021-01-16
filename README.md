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
|GET|auth.login|/login|
|POST||/login|
|GET|auth.logout|/logout|
|GET|dashboard.auth|/dashboard/auth|
|POST||/dashboard/auth|
|POST|auth.dashboard.password|/dashboard/auth/password
|GET|auth.password.forgot|/forgot-password|
|POST||/forgot-password|
|GET|auth.password.reset|/reset-password/{passwordResetToken:token}|
|POST||/reset-password/{passwordResetToken:token}|

## Checking if a user verified their email
Once you've added the `Verifiable` trait (included in the `WanaKinAuth` trait) to your User model, you can use the `emailVerified` attribute to check if the user has verified their email:

```php
if ( $user->emailVerified ) {
...
}
```

## Migrations
This package will register two migrations for the following tables:

* `email_verifications`
* `password_reset_tokens`

Simply run `php artisan migrate` after installing this package for the tables to be created.
