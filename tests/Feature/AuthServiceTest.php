<?php
namespace Tests\Feature;

use WanaKin\Auth\Facades\AuthService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use WanaKin\Auth\Mail\EmailAdded;
use WanaKin\Auth\Mail\PasswordReset;
use Tests\Fixtures\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;

class AuthServiceTest extends FeatureTestCase {
    /**
     * Test registering a user
     *
     * @return void
     */
    public function testRegister()
    {
        // Fake the event facade
        Event::fake();

        // Generate details for the user
        $name = $this->faker->name;
        $email = $this->faker->email;
        $password = $this->faker->password;

        // Fake the Hash facade
        Hash::shouldReceive('make')->once()->withArgs([$password])->andReturns('hashed');

        // Assert the resend method will be called
        AuthService::shouldReceive('resend')->once();
        AuthService::makePartial();

        // Assert the model is returned
        $this->assertNotNull(AuthService::register($name, $email, $password));

        // Assert the model was created
        $this->assertDatabaseHas('users', [
            'name' => $name,
            'email' => $email,
            'password' => 'hashed'
        ]);

        // Assert the Registered event was fired
        Event::assertDispatched(Registered::class);
    }

    /**
     * Test checking the availablility of an email
     *
     * @return void
     */
    public function testEmailAvailable()
    {
        // Create a user
        $email = 'test@example.com';
        $user = $this->createUser([
            'email' => $email
        ]);

        // Add a pending email change
        $user->verifications()->create([
            'email' => 'test2@example.com',
            'verification_slug' => 'abc123'
        ]);

        // Assert the email is not valid
        $this->assertFalse(AuthService::emailAvailable($user->email));
        $this->assertFalse(AuthService::emailAvailable('test2@example.com'));

        // Assert another email is valid
        $this->assertTrue(AuthService::emailAvailable('anothertest@example.com'));

        // Assert the user can update the email to themselves
        $this->assertTrue(AuthService::emailAvailable($user->email, $user));
        $this->assertTrue(AuthService::emailAvailable('test2@example.com', $user));
    }

    /**
     * Test sending the verification email
     *
     * @return void
     */
    public function testResend()
    {
        // Create a user
        $user = $this->createUser();

        // Fake the mail facade
        Mail::fake();

        // Send the verification email
        AuthService::resend($user);

        // Assert the email was sent
        Mail::assertSent(EmailAdded::class, function ($mail) use ($user) {
                return $mail->hasTo($user->email);
            });
    }

    /**
     * Test verifying another email
     *
     * @return void
     */
    public function testResendOther()
    {
        // Create a user an email
        $user = $this->createUser();
        $email = $this->faker->email;

        // Fake the mail facade
        Mail::fake();

        // Send the verification email to the other address
        AuthService::resend($user, $email);

        // Assert the email was sent to the right address
        Mail::assertSent(EmailAdded::class, function ($mail) use ($email) {
                return $mail->hasTo($email);
            });
    }

    /**
     * Test a valid login
     *
     * @return void
     */
    public function testValidLogin()
    {
        // Create a user
        $user = $this->createUser();

        // Attempt to login with the correct password
        $this->assertEquals($user->id, AuthService::login($user->email, 'password')?->id);
    }

    /**
     * Test an invalid login
     *
     * @return void
     */
    public function testInvalidLogin()
    {
        // Create a user
        $user = $this->createUser();

        // Attempt to login with the incorrect password
        $this->assertNull(AuthService::login($user->email, 'abc123'));
    }

    /**
     * Test updating a user's name and email
     *
     * @return void
     */
    public function testUpdateEmail()
    {
        // Create a user
        $user = $this->createUser();

        // Create a new name and email
        $name = $this->faker->name;
        $email = $this->faker->email;

        // Assert that a new email will be sent
        AuthService::shouldReceive('resend')->once()->withArgs(function (User $userArg, string $emailArg) use ($user, $email) {
                return $userArg->id === $user->id && $emailArg === $email;
            });
        AuthService::makePartial();

        // Update only the name
        AuthService::update($user, $name, $user->email);

        // Assert the model was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $name,
            'email' => $user->email
        ]);

        // Update the email
        AuthService::update($user, $name, $email);
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

        // Assert the password is hashed
        Hash::shouldReceive('make')->once()->withArgs([$password])->andReturns('hashed');

        // Update the password
        AuthService::updatePassword($user, $password);

        // Assert the database was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password' => 'hashed'
        ]);
    }

    /**
     * Test a password reset
     *
     * @return void
     */
    public function testResetPassword()
    {
        // Create a user
        $user = $this->createUser();

        // Fake the event facade
        Event::fake();

        // Create a password reset token
        $passwordResetToken = $user->passwordResetTokens()->create([
            'token' => $this->faker->slug
        ]);

        // Reset the password
        $this->assertTrue(AuthService::updatePassword($passwordResetToken, 'abc123'));

        // Assert the event was fired
        Event::assertDispatched(function (PasswordResetEvent $event) use ($user) {
                return $user->is($event->user);
            });
    }

    /**
     * Test verifying an email
     *
     * @return void
     */
    public function testVerify()
    {
        // Create a user
        $user = $this->createUser();

        // Assert the email won't validate with an old verification instance
        $emailVerification = $user->verifications()->create([
            'verification_slug' => 'qwerty',
            'email' => $this->faker->email,
            'created_at' => now()->subHours(4)
        ]);
        $this->assertFalse(AuthService::verify($emailVerification));

        // Update the timestamp to now
        $emailVerification->update([
            'created_at' => now()
        ]);

        // Set the user's email verification to none
        $user->update([
            'email_verified_at' => NULL
        ]);

        // Verify the user's email
        $this->assertTrue(AuthService::verify($emailVerification));

        // Assert the user's email was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $emailVerification->email,
        ]);

        // Assert the verified at date was updated
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        // Set the user's verification timestamp in the future
        $user->update([
            'email_verified_at' => now()->addDays(7),
        ]);

        // Reload the relationship
        $emailVerification->load('verifiable');

        // Assert the email won't validate
        $this->assertFalse(AuthService::verify($emailVerification));
    }

    /**
     * Test sending the password reset link
     *
     * @return void
     */
    public function testSendPasswordRestLink()
    {
        // Create a user
        $user = $this->createUser();

        // Mock the mail facade
        Mail::fake();

        // Request a password reset link
        AuthService::sendPasswordResetLink($user->email);

        // Assert the email was sent
        Mail::assertSent(PasswordReset::class, function ($mail) use ($user) {
                return $mail->hasTo($user->email);
            });
    }
}
