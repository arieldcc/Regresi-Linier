{{-- resources/layouts/admin/main.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/content.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @yield('custom_css')
</head>
<body>
    @include('layouts.admin.partials.navbar')

    {{-- Header / Carousel --}}
    @yield('content')

    @include('layouts.admin.partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
    @yield('custom_js')
</body>
</html>
