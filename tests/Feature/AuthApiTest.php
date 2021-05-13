<?php
namespace Tests\Feature;

use WanaKin\Auth\Facades\AuthService;

class AuthApiTest extends FeatureTestCase
{
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

        // Enable the API routes
        $app['config']->set('auth.routes.api', TRUE);
    }

    /**
     * Test the login API
     *
     * @return void
     */
    public function testApiLogin()
    {
        // Create a user
        $user = $this->createUser();

        // Assert the auth service is called
        AuthService::shouldReceive('login')->withArgs([$user->email, 'password'])->once()->andReturn($user); 

        // Attempt a login
        $response = $this->json('POST', '/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        // Assert a 200
        $response->assertOk();

        // Assert a token was returned
        $this->assertNotNull($response['token']);
    }

    /**
     * Test user registration via the API
     *
     * @return void
     */
    public function testApiRegistration()
    {
        // Create a user
        $user = $this->createUser();
        $password = $this->faker->password;

        // Assert the AuthService is properly called
        AuthService::shouldReceive('register')->once()->withArgs([$user->name, $user->email, $password])->andReturns($user);
        AuthService::shouldReceive('emailAvailable')->once()->withArgs([$user->email])->andReturns(TRUE);

        // Register
        $response = $this->json('POST', '/api/register', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
        ]);

        // Assert a redirect
        $response->assertOk();

        // Assert the user has been logged in
        $this->assertNotNull($response['token']);
    }

    /**
     * Test the registration validation
     *
     * @return void
     */
    public function testRegistrationValidation()
    {
        // Submit a bogus form
        $response = $this->json('POST', '/api/register', [
            'name' => $this->faker->name,
            'email' => 'notarealaddress',
            'password' => $this->faker->password
        ]);

        // Assert a 422 error
        $response->assertStatus(422);

        // Assert a message about the email exists in the response
        $this->assertTrue(array_key_exists('email', $response['errors']));
    }
}
