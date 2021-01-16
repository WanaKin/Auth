@extends( 'auth::centered-card' )

@section( 'content' )
    @include( 'auth::errors' )
    <form method="POST" aciton="{{ route( 'auth.password.reset', ['passwordResetToken' => $passwordResetToken] ) }}">
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
@endsection
