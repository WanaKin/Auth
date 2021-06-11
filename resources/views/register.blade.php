@extends('auth::centered-card')

@section('content')
    @include('auth::errors')
    <form method="POST" action="{{ route('auth.register') }}">
	      @csrf
	      <div class="form-group">
	          <label for="register-name">Name</label>
	          <input class="form-control" type="text" id="register-name" name="name" value="{{ old('name') }}" required>
	      </div>
	      <div class="form-group">
	          <label for="register-email">Email</label>
	          <input class="form-control" type="email" id="register-email" name="email" value="{{ old('email') }}" required>
	      </div>
	      <div class="form-group">
	          <label for="register-password">Password</label>
	          <input class="form-control" type="password" id="register-password" name="password" required>
	      </div>
	      <input class="btn btn-primary" type="submit" value="Register"><br>
	      <a class="small" href="{{ route('login') }}">Already Registered?</a>
    </form>
@endsection
