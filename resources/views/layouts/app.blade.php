<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    {{-- Navbar --}}
    @include('navigation')

    {{-- Optional header image --}}
    @hasSection('header')
        <header class="bg-secondary py-5 text-white text-center mb-4">
            @yield('header')
        </header>
    @endif

    {{-- Main Content --}}
    <main class="container flex-grow-1 mb-4">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-white text-center py-3 border-top mt-auto">
        &copy; {{ date('Y') }} Prediksi tingkat Konsumsi Air PDAM
    </footer>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
