<?php
namespace WanaKin\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use WanaKin\Auth\Facades\AuthService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;

class AuthApiController extends Controller {
    /**
     * Make sure Sanctum is installed
     * 
     * @return void
     */
    public function __construct() {
        if ( !class_exists( \Laravel\Sanctum\Sanctum::class ) ) {
            throw new \RuntimeException( 'Please install Laravel Sanctum to use API authentication.' );            
        }
    }

    /**
     * Log a user in
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login( Request $request ) : JsonResponse {
        // Verify the username and password
        $creds = $request->validate( [
            'email' => 'required|email',
            'password' => 'required'
        ] );

        // Attempt a login
        if ( $user = AuthService::login( $creds['email'], $creds['password'] ) ) {
            // Issue a token
            $token = $user->createToken( Str::random() );

            return response()->json( [
                'message' => 'Log in successful.',
                'token' => $token->plainTextToken
            ] );
        } else {
            return response()->json( [
                'message' => 'Incorrect email or password.'
            ] );
        }
    }

    /**
     * Register a user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register( Request $request ) : JsonResponse {
        // Validation
        $creds = $request->validate( array_merge( [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ], config( 'auth.validation.register', [] ) ) );
        
         // Make sure the email is available
         if ( !AuthService::emailAvailable( $creds['email'] ) ) {
            return redirect()->back()->withInput()->withErrors( [
                'email' => 'That email is invalid and/or unavailable.'
            ] );
        }

        // Register the user
        if ( $user = AuthService::register( $creds['name'], $creds['email'], $creds['password'] ) ) {
            // Log the user in
            $token = $user->createToken( Str::random() );

            return response()->json( [
                'message' => 'Registration successful.',
                'token' => $token->plainTextToken
            ] );            
        }       
    }
}