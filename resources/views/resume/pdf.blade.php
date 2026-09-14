<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>{{ $fullName }} — Ma'lumotnoma</title>
    <style>
        {!! file_get_contents(resource_path('css/resume-pdf.css')) !!}
    </style>
</head>
<body>
    @include('resume.partials.document-content', ['docClass' => 'doc--pdf'])
</body>
</html>
