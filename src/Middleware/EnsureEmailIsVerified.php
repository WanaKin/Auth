<?php
namespace WanaKin\Auth\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class EnsureEmailIsVerified {
    /**
     * Handle a request
     *
     * @param Request $request
     * @param \Closure $next
     * @param ?string $redirectToRoute = NULL
     * @return mixed
     */
    public function handle( Request $request, \Closure $next, ?string $redirectToRoute = NULL ) {
        // If no user is defined or the email is not verified
        if ( !$request->user() || !$request->user()->emailVerified ) {
            $request->session()->flash( 'email_verification_required', TRUE );
            return $request->expectsJson() ? abort( 403, 'Your email address is not verified.' ) : Redirect::route( $redirectToRoute ?: 'auth.dashboard' );
        }
        

        return $next( $request );
    }
}
