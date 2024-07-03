<!doctype html>
<html lang="en" dir="ltr">
  <head>

		<!-- META DATA -->
		<meta charset="UTF-8">
		<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<!-- FAVICON -->
		<link rel="shortcut icon" type="image/x-icon" href="{{asset('images/sawit.svg')}}" />

         <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
		<!-- TITLE -->
		<title>{{ config('app.name', 'Laravel') }}</title>


		<!-- STYLE CSS -->
		<link href="{{asset('assets/css/style.css')}}" rel="stylesheet"/>
		<link href="{{asset('assets/css/plugins.css')}}" rel="stylesheet"/>

		<!--- FONT-ICONS CSS -->
		<link href="{{asset('assets/css/icons.css')}}" rel="stylesheet"/>
         <!-- Scripts -->
        @vite(['resources/js/app.js'])

	</head>

	<body class="bg-white bg-gradient">

		<div>

			<!-- GLOABAL LOADER -->
			<div id="global-loader">
				<img src="{{asset('assets/images/loader.svg')}}" class="loader-img" alt="Loader">
			</div>
			<!-- /GLOABAL LOADER -->
            @yield('content')


		</div>

		<script src="{{asset('assets/js/jquery.min.js')}}"></script>

        <script src="{{asset('assets/js/custom.js')}}"></script>

	</body>
</html>
