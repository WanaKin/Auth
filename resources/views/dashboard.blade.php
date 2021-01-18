@extends( 'auth::base' )

@section( 'main' )
    <div class="row align-items-center min-vh-100">
	<div class="mx-auto w-100">
	    @include( 'auth::errors' )
	    @if ( Session::has( 'email_verification_required' ) )
		<div class="alert alert-danger">
		    That feature requires a verified email.
		</div>
	    @endif
	    @if ( Session::has( 'verification_send' ) )
		<div class="alert alert-success">
		    A verification email has been sent.
		</div>
	    @endif
	    @if ( !$user->emailVerified )
		<div class="alert alert-warning">
		    Your email has not been verified. Some features may be disabled.<br/>
		    Didn't receive a verification email? <a href="{{ route( 'auth.dashboard.resend' ) }}">Click here to send another</a>.
		</div>
	    @endif
	    <div class="card m-2">
		<div class="card-body">
		    <h5 class="card-title">Name & Email</h5>
		    <form method="POST" action="{{ route( 'auth.dashboard' ) }}">
			@csrf
			<div class="form-group">
			    <label for="name">Name</label>
			    <input class="form-control" type="text" id="name" name="name" value="{{ old( 'name' ) ?? $user->name }}" required>
			</div>
			<div class="form-group">
			    <label for="email">Email</label>
			    <br/><small>Please note that your email will not be updated until it is verified.</small>
			    <input class="form-control" type="email" id="email" name="email" value="{{ old( 'email' ) ?? $user->email }}" required>
			</div>
			<input class="btn btn-primary" type="submit" value="Update">
		    </form>
		</div>
	    </div>

	    <div class="card m-2">
		<div class="card-body">
		    <h5 class="card-title">Password</h5>
		    <form method="POST" action="{{ route( 'auth.dashboard.password' ) }}">
			@csrf
			<div class="form-group">
			    <label for="password">New Password</label>
			    <input class="form-control" type="password" id="password" name="password" required>
			</div>
			<div class="form-group">
			    <label for="password-verify">Verify</label>
			    <input class="form-control" type="password" id="password-verify" name="password-verify" required>
			</div>
			<input class="btn btn-primary" type="submit" value="Update">
		    </form>
		</div>
	    </div>
	</div>
    </div>
@endsection
