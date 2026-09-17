<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dompet Sadhar - @yield('title', 'Dashboard Kopma')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/kLayout.css') }}">
    @stack('styles')
</head>
<body>

<button class="nav-toggle" id="navToggle">
    <span></span><span></span><span></span>
</button>

<nav class="navigation" id="navigation">
    <ul>
        <li class="{{ request()->path() == 'kDashboard' ? 'active' : '' }}">
            <a href="{{ url('/kDashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li class="{{ request()->path() == 'kVerifikasi' ? 'active' : '' }}">
            <a href="{{ url('/kVerifikasi') }}"><i class="fas fa-check-double"></i> Verifikasi</a>
        </li>
        <li class="{{ request()->path() == 'kReward' ? 'active' : '' }}">
            <a href="{{ url('/kReward') }}"><i class="fas fa-gift"></i> Reward</a>
        </li>
    </ul>
</nav>
<div class="overlay" id="overlay"></div>

@yield('content')

<script>
    const navToggle = document.getElementById('navToggle');
    const navigation = document.getElementById('navigation');
    const overlay = document.getElementById('overlay');
    
    if (navToggle && navigation && overlay) {
        navToggle.addEventListener('click', function() {
            navToggle.classList.toggle('active');
            navigation.classList.toggle('active');
            overlay.classList.toggle('active');
        });
        
        overlay.addEventListener('click', function() {
            navToggle.classList.remove('active');
            navigation.classList.remove('active');
            overlay.classList.remove('active');
        });
    }
</script>

@stack('scripts')
</body>
</html>