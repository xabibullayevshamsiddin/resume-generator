<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', "Ma'lumotnoma generatori")</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600&display=swap">
    <link rel="stylesheet" href="{{ frontend_asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ frontend_asset('css/home.css') }}">
</head>
<body class="body--home">
    @yield('content')

    <script src="{{ frontend_asset('js/app.js') }}" defer></script>
    <script src="{{ frontend_asset('js/home.js') }}" defer></script>
</body>
</html>
