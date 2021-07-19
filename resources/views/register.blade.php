@extends('auth::centered-card')

@section('content')
    <form method="POST" action="{{ route('auth.register') }}">
	      @csrf
	      <div class="form-group">
	          <label for="register-name">Name</label>
	          <input class="form-control{{ $errors->has('name') ? ' is-invalid' : null }}" type="text" id="register-name" name="name" value="{{ old('name') }}">
            @if ($errors->has('name'))
                <div class="invalid-feedback">
                    {{ $errors->first('name') }}
                </div>
            @endif
	      </div>
	      <div class="form-group">
	          <label for="register-email">Email</label>
	          <input class="form-control{{ $errors->has('email') ? ' is-invalid' : null }}" type="text" id="register-email" name="email" value="{{ old('email') }}">
            @if ($errors->has('email'))
                <div class="invalid-feedback">
                    {{ $errors->first('email') }}
                </div>
            @endif
	      </div>
	      <div class="form-group">
	          <label for="register-password">Password</label>
	          <input class="form-control{{ $errors->has('password') ? ' is-invalid' : null }}" type="password" id="register-password" name="password">
            @if ($errors->has('password'))
                <div class="invalid-feedback">
                    {{ $errors->first('password') }}
                </div>
            @endif
	      </div>
	      <input class="btn btn-primary" type="submit" value="Register"><br>
	      <a class="small" href="{{ route('login') }}">Already Registered?</a>
    </form>
@endsection
