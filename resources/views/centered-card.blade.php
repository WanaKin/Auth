@extends( 'auth::base' )

@section( 'main' )
    <div class="row align-items-center min-vh-100">
	      <div class="col-lg-6 mx-auto">
	          <div class="p-4 bg-white">
		                <h1 class="card-title text-center">{{ config('app.name') }}</h1>
		                @yield('content')
	          </div>
	      </div>
    </div>
@endsection
