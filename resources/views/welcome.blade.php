<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZeroWasteMess.io</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-slate-50 text-slate-800">
    <div class="min-h-screen flex flex-col items-center justify-center gap-6">
        <h1 class="text-3xl font-bold text-emerald-600">ZeroWasteMess.io</h1>
        <p class="text-slate-500">Kamar-Taj Refectory Resource Engine</p>
        <div class="flex gap-4">
            <a href="{{ route('student.login') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Student Login</a>
            <a href="{{ route('contractor.login') }}" class="px-4 py-2 bg-blue-800 text-white rounded-lg">Contractor Login</a>
        </div>
    </div>
</body>
</html>
