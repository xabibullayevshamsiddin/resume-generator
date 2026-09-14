<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', "Ma'lumotnoma generatori")</title>
    <link rel="stylesheet" href="{{ frontend_asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ frontend_asset('css/resume-form.css') }}">
    <link rel="stylesheet" href="{{ frontend_asset('css/resume-pdf.css') }}">
</head>
<body>
    <main class="page-container">
        @yield('content')
    </main>

    <script src="{{ frontend_asset('js/app.js') }}" defer></script>
    <script src="{{ frontend_asset('js/resume-form.js') }}" defer></script>
</body>
</html>
