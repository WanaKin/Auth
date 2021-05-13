<?php
namespace Tests\Feature;

use WanaKin\Auth\Facades\AuthService;
use WanaKin\Auth\EmailVerification;
use WanaKin\Auth\PasswordResetToken;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class AuthHttpTest extends FeatureTestCase {
    /**
     * Set up for HTTP tests
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // This can be hard coded because its just for testing
        $app['config']->set('app.key', 'base64:tGJVkbucyT3kXa+UU9hqW28KRYNFYh+5cTTxxOUQRVw=');
    }

    /**
     * Assert the login route has been registered
     *
     * @return void
     */
    public function testLoginRouteName()
    {
        // Try and visit the dashboard without logging in
        $response = $this->get('/dashboard/auth');

        // Assert a redirect to /login
        $response->assertRedirect('/login');
    }

    /**
     * Test registering a user
     *
     * @return void
     */
    public function testRegister()
    {
        // Create a user
        $user = $this->createUser();
        $password = $this->faker->password;

        // Assert the AuthService is properly called
        AuthService::shouldReceive('register')->once()->withArgs([$user->name, $user->email, $password])->andReturns($user);
        AuthService::shouldReceive('emailAvailable')->once()->withArgs([$user->email])->andReturns(TRUE);

        // Register
        $response = $this->post('/register', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
        ]);

        // Assert a redirect
        $response->assertRedirect();

        // Assert the user has been logged in
        $this->assertAuthenticatedAs($user);
    }


    /**
     * Test validation
     *
     * @return void
     */
    public function testRegistrationValidation()
    {
        // Set a unique constraint on the name
        config()->set('auth.validation.register', [
            'name' => 'required|unique:users,name'
        ]);

        // Create a user
        $user = $this->createUser();

        // Attempt to create a user with the same name
        $response = $this->post('/register', [
            'name' => $user->name,
            'email' => $this->faker->email,
            'password' => 'password'
        ]);

        // Assert a name validation error is set
        $response->assertSessionHasErrors(['name']);
    }


    /**
     * Test logging in
     *
     * @return void
     */
    public function testLogin()
    {
        // Create a user
        $user = $this->createUser();
        $password = 'password';

        // Mock the AuthService
        AuthService::shouldReceive('login')->once()->withArgs([$user->email, $password])->andReturn($user);

        // Mock the event facade
        Event::fake();

        // Set the redirect
        config()->set('auth.redirect', '/thisisatest');

        // Attempt a login
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        // Assert the user was redirected properly
        $response->assertRedirect('/thisisatest');

        // Assert the user has been logged in
        $this->assertAuthenticatedAs($user);

        // Assert the event was dispatched
        Event::assertDispatched(Login::class);
    }

    /**
     * Test Updating user details
     *
     * @return void
     */
    public function testUpdate()
    {
        // Create a user
        $user = $this->createUser();

        // Generate a new name and email
        $name = $this->faker->name;
        $email = $this->faker->email;

        // Assert the service is called properly
        AuthService::shouldReceive('update')->once()->withArgs([$user, $name, $email]);
        AuthService::shouldReceive('emailAvailable')->once()->withArgs([$email])->andReturn(TRUE);

        // Sign in
        $this->actingAs($user);

        // Update
        $response = $this->post('/dashboard/auth', [
            'name' => $name,
            'email' => $email
        ]);

        // Assert the user is redirected back to the dashboard
        $response->assertRedirect();
    }

    /**
     * Test the update validation rules
     *
     * @return void
     */
    public function testUpdateValidation()
    {
        // Create a user
        $user = $this->createUser();

        // Create a new constraint on the name
        config()->set('auth.validation.update', [
            'name' => 'required|min:150'
        ]);

        // Log in
        $this->actingAs($user);

        // Update the user
        $response = $this->post('/dashboard/auth', [
            'name' => $user->name,
            'email' => $user->email
        ]);

        // Assert a validation error has been created
        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test verifying an email
     *
     * @return void
     */
    public function testVerifyEmail()
    {
        // Create a user
        $user = $this->createUser();

        // Create an email verification database entry
        $emailVerification = $user->verifications()->create([
            'email' => $user->email,
            'verification_slug' => 'myslug'
        ]);

        // Assert the verify method will be called on the service
        AuthService::shouldReceive('verify')->once()->withArgs(function (EmailVerification $emailVerificationArg) use ($emailVerification) {
                return $emailVerification->is($emailVerificationArg);
            })->andReturn(TRUE);

        // Attempt to verify the email
        $response = $this->get('/verify-email/' . $emailVerification->verification_slug);

        // Assert a redirect
        $response->assertRedirect();

        // Assert the user was logged in
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test updating a user's password
     *
     * @return void
     */
    public function testUpdatePassword()
    {
        // Create a user
        $user = $this->createUser();

        // Create a new password
        $password = $this->faker->password;

        // Assert the updatePassword method is called on the service
        AuthService::shouldReceive('updatePassword')->once()->withArgs([$user, $password]);

        // Log in
        $this->actingAs($user);

        // Update the password
        $response = $this->post('/dashboard/auth/password', [
            'password' => $password,
            'password-verify' => $password
        ]);

        // Assert a redirect
        $response->assertRedirect();
    }

    /**
     * Test requesting a forgot password link
     *
     * @return void
     */
    public function testForgotPassword()
    {
        // Create a user
        $user = $this->createUser();

        // Assert the service is called
        AuthService::shouldReceive('sendPasswordResetLink')->once()->withArgs([$user->email]);

        // Request a password reset link
        $response = $this->post('/forgot-password', [
            'email' => $user->email
        ]);

        // Assert a redirect
        $response->assertRedirect();
    }

    /**
     * Test reseting a user's password
     *
     * @return void
     */
    public function testResetPassword()
    {
        // Create a user and reset link
        $user = $this->createUser();
        $passwordResetToken = $user->passwordResetTokens()->create([
            'token' => $this->faker->slug
        ]);

        // Generate a password
        $password = $this->faker->password;

        // Assert the service is called
        AuthService::shouldReceive('updatePassword')->once()->withArgs(function (PasswordResetToken $passwordResetTokenArg, string $passwordArg) use ($passwordResetToken, $password) {
                return $passwordResetToken->is($passwordResetTokenArg) && $passwordArg === $password;
            });

        // Reset the password
        $response = $this->post('/password-reset/' . $passwordResetToken->token, [
            'password' => $password,
            'password-verify' => $password
        ]);

        // Assert the response is a redirect
        $response->assertRedirect();
    }

    /**
     * Test request a new verification email
     *
     * @return void
     */
    public function testResend()
    {
        // Create new user
        $user = $this->createUser();

        // Assert the AuthService is called
        AuthService::shouldReceive('resend')->once()->withArgs([$user])->andReturn(TRUE);

        // Request to resend the verification email
        $this->actingAs($user);
        $response = $this->get('/dashboard/auth/resend');

        $response->assertRedirect();
    }

    /**
     * Test logging out
     *
     * @return void
     */
    public function testLogout()
    {
        // Create a user
        $user = $this->createUser();

        // Mock the event facade
        Event::fake();

        // Log in
        $this->actingAs($user);

        // Logout
        $response = $this->get('/logout');

        // Assert a redirect
        $response->assertRedirect();

        // Assert the user is logged out
        $this->assertGuest();

        // Assert the logout event was dispatched
        Event::assertDispatched(Logout::class);
    }
}
