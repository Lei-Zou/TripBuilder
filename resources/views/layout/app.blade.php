<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Flight Ticket</title>
    <link rel="stylesheet" href="/css/app.css">

</head>
<body>
        @if(count($errors) > 0)
            @foreach($errors -> all() as $error)
                <div class="alert alert-danger">
                    {{ $error }}
                </div>
            @endforeach
        @endif

        @yield('content')
</body>
@stack('scripts')
{!! Html::script('js/trip.js') !!}
</html>