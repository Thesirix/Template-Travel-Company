<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $brand->primary() }}">
    <title>@yield('title', $brand->name())</title>
    @include('layouts.partials.favicon')
    <script>try { if (localStorage.getItem('tc-sidebar') === 'collapsed' && innerWidth >= 992) document.documentElement.classList.add('tc-collapsed'); } catch (e) {}</script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.8/build/css/intlTelInput.css">
    <link rel="stylesheet" href="{{ asset('css/tc/theme.css') }}?v={{ filemtime(public_path('css/tc/theme.css')) }}">
    <style>:root{ {!! $brand->cssVariables() !!} }</style>

    @yield('styles')

    {{-- Chargés dans l'en-tête : beaucoup de pages ont des scripts jQuery écrits directement dans leur contenu --}}
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</head>
<body id="page-top">
<div id="wrapper">
    @yield('SideBar')
    <div class="tc-backdrop"></div>

    <div id="content-wrapper">
        <div id="content">
            @yield('TopBar')

            <main class="tc-page">
                @yield('content')
            </main>

            <div class="spinner-overlay">
                <div class="spinner-border" role="status"><span class="sr-only">Chargement…</span></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/tc/ui.js') }}?v={{ filemtime(public_path('js/tc/ui.js')) }}"></script>
@include('layouts.partials.flash')
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.8/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.8/build/js/utils.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="{{ asset('assets/vendor/chart.js/Chart.js') }}"></script>
<script>
    if (window.Chart) {
        Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
        Chart.defaults.color = '#8a92ad';
    }
</script>
<script src="{{ asset('assets/vendor/moment/moment.js') }}"></script>
<script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/vendor/flatpickr/flatpickr.js') }}"></script>
@stack('script')
</body>
</html>
