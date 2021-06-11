<!DOCTYPE html>
<html>
    <head>
	      <!-- Meta -->
	      <meta charset="utf-8">
	      <meta name="viewport" content="width=device-width, intial-scale=1, shrink-to-fit=no">
	      <title>{{ config('app.name') }}</title>

	      <!-- Bootstrap CSS -->
	      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    </head>
    <body class="bg-light">
	      <main class="container min-vh-100">
	          @yield( 'main' )
	      </main>
    </body>
</html>
