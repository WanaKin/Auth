<?php
namespace WanaKin\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use WanaKin\Auth\AuthService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Authenticatable;

class AuthController extends Controller
{
    /**
     * Inject the current user id
     *
     * @param  Request $request
     * @param  array $rules
     * @return array
     */
    protected function parse(Request $request, array $rules) : array
    {
        // Iterate over the array and replace {userId} with the actual user id
        $userId = $request->user()->id;

        foreach ($rules as $key => $rule) {
            $rules[$key] = (string)Str::of($rule)->replace('{userId}', $userId);
        }

        return $rules;
    }

    /**
     * Log in
     *
     * @param  Authenticatable $user
     * @param  string $state = 'login'
     * @param  ?string $route = NULL
     * @return RedirectResponse
     */
    protected function loginAndRedirect(Authenticatable $user, string $state = 'login', ?string $route = NULL) : RedirectResponse
    {
        // Log the user in if not already
        if (!(($currentUser = request()->user()) && $currentUser->id == $user->id)) {
            // Use remember if it exists in the request
            Auth::login($user, request()->has('remember'));
        }

        // If a route is provided, use that
        if ($route) {
            return redirect($route);
        }

        // Try to redirect to the route specific to the current state, fallback to a global redirect, and if all else fails, redirect to the application's root URL
        if (is_string($route = config('auth.redirect.' . $state) ?? config('auth.redirect'))) {
            return redirect($route);
        } else {
            return redirect(config('app.url'));
        }
    }

    /**
     * Show the registration page
     *
     * @return Response
     */
    public function showRegistrationPage() : Response
    {
        return response()->view('auth::register');
    }

    /**
     * Register a user
     *
     * @param  Request $request
     * @param  AuthService $authService
     * @return RedirectResponse
     */
    public function register(Request $request, AuthService $authService) : RedirectResponse
    {
        // Validation
        $creds = $request->validate(array_merge([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ], config('auth.validation.register', [])));

        // Make sure the email is available
        if (!$authService->emailAvailable($creds['email'])) {
            return redirect()->back()->withInput()->withErrors([
                'email' => 'That email is invalid and/or unavailable.'
            ]);
        }

        // Register the user
        if ($user = $authService->register($creds['name'], $creds['email'], $creds['password'])) {
            // Log the user in
            return $this->loginAndRedirect($user, 'register');
        }
    }

    /**
     * Show the login page
     *
     * @return Response
     */
    public function showLoginPage() : Response
    {
        return response()->view('auth::login');
    }

    /**
     * Log a user in
     *
     * @param  Request $request
     * @param  AuthService $authService
     * @return RedirectResponse
     */
    public function login(Request $request, AuthService $authService) : RedirectResponse
    {
        // Validation
        $creds = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Attempt the login
        if ($user = $authService->login($creds['email'], $creds['password'])) {
            return $this->loginAndRedirect($user, 'login');
        } else {
            return redirect()->back()->withInput()->withErrors([
                'email' => 'The provided email and/or password is incorrect or invalid.'
            ]);
        }
    }

    /**
     * Log a user out
     *
     * @return RedirectResponse
     */
    public function logout() : RedirectResponse
    {
        Auth::logout();
        return redirect()->route('login');
    }

    /**
     * Show the dashboard
     *
     * @param  Request $request
     * @return Response
     */
    public function dashboard(Request $request) : Response
    {
        return response()->view('auth::dashboard', [
            'user' => $request->user()
        ]);
    }

    /**
     * Update the name and email
     *
     * @param  Request $request
     * @param  AuthService $authService
     * @return RedirectResponse
     */
    public function update(Request $request, AuthService $authService) : RedirectResponse
    {
        $creds = $request->validate(array_merge([
            'name' => 'required',
            'email' => 'required|email'
        ], Arr::only($this->parse($request, config('auth.validation.update', [])), ['name','email'])));

        // If the email has changed, require it to be unique
        if ($creds['email'] !== $request->user()->email) {
            if (!$authService->emailAvailable($creds['email'])) {
                return redirect()->back()->withInput()->withErrors([
                    'email' => 'That email is invalid and/or unavailable.'
                ]);
            }
        }

        // Update the settings
        $authService->update($request->user(), $creds['name'], $creds['email']);

        // Redirect back to the dashboard
        return redirect()->back();
    }

    /**
     * Update a user's password
     *
     * @param  Request $request
     * @param  AuthService $authService
     * @return RedirectResponse
     */
    public function updatePassword(Request $request, AuthService $authService) : RedirectResponse
    {
        // Validation
        $creds = $request->validate([
            'password' => 'required|min:6',
            'password-verify' => 'required|same:password'
        ], [], [
            'password-verify' => 'password verification'
        ]);

        // Update the password
        $authService->updatePassword($request->user(), $creds['password']);

        // Return the dashboard
        return redirect()->back();
    }

    /**
     * Verify an email
     *
     * @param  AuthService $authService
     * @param  EmailVerification $emailVerification
     * @return RedirectResponse
     */
    public function verify(AuthService $authService, EmailVerification $emailVerification) : RedirectResponse
    {
        if ($authService->verify($emailVerification)) {
            // Log in and redirect to the dashbord
            return $this->loginAndRedirect($emailVerification->verifiable, 'verify');
        }
    }

    /**
     * Show the forgot password form
     *
     * @return Response
     */
    public function showForgotPasswordPage() : Response
    {
        return response()->view('auth::forgot-password');
    }

    /**
     * Reset the password
     *
     * @param  Request $request
     * @param  AuthService $authService
     * @return RedirectResponse
     */
    public function forgotPassword(Request $request, AuthService $authService) : RedirectResponse
    {
        // Validation
        $creds = $request->validate([
            'email' => 'required|email'
        ]);

        // Send the email
        $authService->sendPasswordResetLink($creds['email']);

        // Update the status
        $request->session()->flash('reset_link_sent', TRUE);

        return redirect()->back()->withInput();
    }

    /**
     * Show the password reset form
     *
     * @param  PasswordResetToken $passwordResetToken
     * @return Response
     */
    public function showResetPasswordPage(PasswordResetToken $passwordResetToken) : Response
    {
        return response()->view('auth::reset-password', [
            'passwordResetToken' => $passwordResetToken
        ]);
    }

    /**
     * Reset the password
     *
     * @param  Request $request
     * @param  AuthService $authService
     * @param  PasswordResetToken $passwordResetToken
     * @return RedirectResponse
     */
    public function resetPassword(Request $request, AuthService $authService, PasswordResetToken $passwordResetToken) : RedirectResponse
    {
        // Validation
        $creds = $request->validate([
            'password' => 'required|min:6',
            'password-verify' => 'required|same:password'
        ], [], [
            'password-verify' => 'password verification'
        ]);

        // Reset the password
        if ($authService->updatePassword($passwordResetToken, $creds['password'])) {
            // Return to the log in page
            $request->session()->flash('password_reset', TRUE);
            return redirect()->route('login');
        } else {
            return redirect()->back()->withErrors([
                'password' => 'Your password reset link has expired. Please request a new one.'
            ]);
        }
    }

    /**
     * Resend the verification email
     *
     * @param  Request $request
     * @param  AuthService $authService
     * @return RedirectResponse
     */
    public function resend(Request $request, AuthService $authService) : RedirectResponse
    {
        // Resend the verification email
        $authService->resend($request->user());

        $request->session()->flash('verification_send', TRUE);

        return redirect()->back();
    }
}
