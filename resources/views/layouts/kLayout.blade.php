<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Dompet Sadhar - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/wLayout.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Stack untuk nampung -->
     @stack('styles')
</head>
<body>

<button class="nav-toggle" id="navToggle">
    <span></span>
    <span></span>
    <span></span>
</button>

<nav class="navigation" id="navigation">
    <ul>
        <li class="{{ $title === 'Dashboard' ? 'active' : '' }}"  >
            <a href ="/kDashboard">
            <i class="fas fa-tachometer-alt" ></i>Dashboard</a>
        </li>
        <li class="{{ $title === 'Verifikasi' ? 'active' : '' }}">
            <a href ="/kVerifikasi">
                <i class="fas fa-check-double" ></i>Verifikasi</a>        
        </li>
        <li class="{{ $title === 'Reward' ? 'active' : '' }}" >
            <a href ="/kReward">
                <i class="fas fa-gift"></i>Reward</a>
        </li>
    </ul>
</nav>
<div class="overlay" id="overlay"></div>
    @yield('content')
    @stack('scripts')
</body>
</html>
