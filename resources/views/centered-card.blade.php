@extends( 'auth::base' )

@section( 'main' )
    <div class="row align-items-center min-vh-100">
	      <div class="col-lg-6 mx-auto">
	          <div class="card p-4" style="border-radius: 3em;">
		            <div class="card-content">
		                <h1 class="card-title text-center">{{ config('app.name') }}</h1>
		                @yield('content')
		            </div>
	          </div>
	      </div>
    </div>
@endsection
