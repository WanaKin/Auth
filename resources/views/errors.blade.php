@if ( isset( $errors ) && $errors->any() )
    <p id="errors">
    @foreach ( $errors->all() as $error )
	<strong class="text-danger">{{ $error }}</strong>

	@if ( !$loop->last )
	    <br/>
	@endif
    @endforeach
    </p>
@endif
