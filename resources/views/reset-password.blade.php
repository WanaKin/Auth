@extends( 'auth::centered-card' )

@section( 'content' )
    <form method="POST" aciton="{{ route( 'auth.password.reset', ['passwordResetToken' => $passwordResetToken] ) }}">
	@csrf
	<div class="form-group">
	    <label for="password">New Password</label>
	    <input class="form-control{{ $errors->has('password') ? ' is-invalid' : null }}" type="password" id="password" name="password">
      @if ($errors->has('password'))
          <div class="invalid-feedback">
              {{ $errors->first('password') }}
          </div>
      @endif
	</div>
	<div class="form-group">
	    <label for="password-verify">Verify</label>
	    <input class="form-control{{ $errors->has('password-verify') ? ' is-invalid' : null }}" type="password" id="password-verify" name="password-verify">
      @if ($errors->has('password-verify'))
          <div class="invalid-feedback">
              {{ $errors->first('password-verify') }}
          </div>
      @endif
	</div>
	<input class="btn btn-primary" type="submit" value="Update">
    </form>
@endsection
