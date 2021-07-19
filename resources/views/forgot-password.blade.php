@extends('auth::centered-card')

@section('content')
    @if (Session::has('reset_link_sent') && Session::get('reset_link_sent'))
	      <p><strong class="text-success">If an account with that email exists, a password reset link has been sent.</strong></p>
    @endif
    <span class="text-muted small">Simply fill out your email below, and we'll send you a password reset link.</span>
    <form method="POST" action="{{ route('auth.password.forgot') }}">
	      @csrf
	      <div class="form-group">
	          <label for="login-email">Account Email</label>
	          <input class="form-control{{ $errors->has('email') ? ' is-invalid' : null }}" type="text" id="login-email" name="email" value="{{ old('email') }}">
            @if ($errors->has('email'))
                <div class="invalid-feedback">
                    {{ $errors->first('email') }}
                </div>
            @endif
	      </div>
	      <input class="btn btn-primary" type="submit" value="Go!">
    </form>
@endsection
