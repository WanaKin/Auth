<?php
namespace Tests\Feature;

use Orchestra\Testbench\TestCase;
use Tests\Fixtures\User;
use Illuminate\Foundation\Testing\WithFaker;
use WanaKin\Auth\AuthServiceProvider;
use Laravel\Sanctum\SanctumServiceProvider;

class FeatureTestCase extends TestCase {
    use WithFaker;
    
    /**
     * Set up before each test
     *
     * @return void
     */
    public function setUp() : void {
        parent::setUp();

        // Load and run migrations
        $this->loadLaravelMigrations();
        $this->artisan( 'migrate' )->run();

        // Set the user model
        config()->set( 'auth.providers.users.model', User::class );
    }

    /**
     * Create a user
     *
     * @param array $args = []
     * @return void
     */
    public function createUser( array $args = [] ) : User {
        return User::create( array_merge( [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'password' => '$2y$10$obP//QcLS4VgeDgkIesqluxNwz78nNOM9keum3BUR1yDHoyUlcG1m'
        ], $args ) );
    }

    /**
     * Load the package provider
     *
     * @return array
     */
    protected function getPackageProviders( $app ) {
        return [AuthServiceProvider::class, SanctumServiceProvider::class];
    }
}
