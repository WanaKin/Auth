@extends( 'auth::centered-card' )

@section( 'content' )
    @include( 'auth::errors' )
    @if ( Session::has( 'password_reset' ) )
	<p><strong class="text-success">Your password has been reset. Please log in with your new password.</strong></p>
    @endif
    <form method="POST" action="{{ route( 'auth.login' ) }}">
	@csrf
	<div class="form-group">
	    <label for="login-email">Email</label>
	    <input class="form-control" type="email" id="login-email" name="email" value="{{ old( 'email' ) }}" required>
	</div>
	<div class="form-group">
	    <label for="login-password">Password</label>
	    <input class="form-control" type="password" id="login-password" name="password" required>
	</div>
	<input class="d-block btn btn-primary" type="submit" value="Login">
	<a href="{{ route( 'auth.register' ) }}">Don't have an account?</a><br/>
	<a href="{{ route( 'auth.password.forgot' ) }}">Lost Your Password?</a>
    </form>
@endsection
