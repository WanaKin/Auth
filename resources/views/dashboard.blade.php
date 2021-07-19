@extends('auth::base')

@section('main')
    <div class="row align-items-center min-vh-100">
	      <div class="mx-auto w-100">
	          @if (Session::has('email_verification_required'))
		            <div class="alert alert-danger">
		                That feature requires a verified email.
		            </div>
	          @endif
	          @if (Session::has('verification_send'))
		            <div class="alert alert-success">
		                A verification email has been sent.
		            </div>
	          @endif
	          @if (!$user->emailVerified)
		            <div class="alert alert-warning">
		                Your email has not been verified. Some features may be disabled.<br/>
		                Didn't receive a verification email? <a href="{{ route('auth.dashboard.resend') }}">Click here to send another</a>.
		            </div>
	          @endif
	          <div class="p-4 bg-white mb-2">
		                <h5 class="card-title">Name & Email</h5>
		                <form method="POST" action="{{ route('auth.dashboard') }}">
			                  @csrf
			                  <div class="form-group">
			                      <label for="name">Name</label>
			                      <input class="form-control{{ $errors->has('name') ? ' is-invalid' : null }}" type="text" id="name" name="name" value="{{ old('name') ?? $user->name }}">
                            @if ($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
			                  </div>
			                  <div class="form-group">
			                      <label class="d-block mb-0" for="email">Email</label>
			                      <span class="text-muted small">Please note that your email will not be updated until it is verified.</span>
			                      <input class="form-control{{ $errors->has('email') ? ' is-invalid' : null }}" type="text" id="email" name="email" value="{{ old('email') ?? $user->email }}">
                            @if ($errors->has('email'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif
			                  </div>
			                  <input class="btn btn-primary" type="submit" value="Update">
		                </form>
	          </div>

	          <div class="p-4 bg-white">
		                <h5 class="card-title">Password</h5>
		                <form method="POST" action="{{ route('auth.dashboard.password') }}">
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
	          </div>
	      </div>
    </div>
@endsection
