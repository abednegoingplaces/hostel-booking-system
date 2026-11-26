<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hostel Booking') | Hostel Booking System</title>
    <link rel="stylesheet" href="{{ asset('css/hostel.css') }}">
</head>
<body>
    @include('partials.nav')

    <main class="wrap">
        @include('partials.flash')
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
