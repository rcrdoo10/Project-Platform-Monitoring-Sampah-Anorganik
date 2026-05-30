<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Dompet Sadhar - Dashboard Sampah Plastik</title>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Chart.js CDN - HARUS dipanggil SEBELUM script kustom -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- External CSS -->
    <link rel="stylesheet" href="css/wDashboard.css">
</head>
<body>

<button class="nav-toggle" id="navToggle">
    <span></span>
    <span></span>
    <span></span>
</button>

<nav class="navigation" id="navigation">
    <ul>
        <li data-page="dashboard" class="active">
            <a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li data-page="verifikasi">
            <a href="#"><i class="fas fa-check-circle"></i> Poin</a>
        </li>
        <li data-page="reward">
            <a href="#"><i class="fas fa-gift"></i> Reward</a>
        </li>
    </ul>
</nav>
<div class="overlay" id="overlay"></div>

<main class="dashboard-container" id="mainContent">
    <!-- Header dengan sapaan dan logout -->
    <div class="header-flex">
        <div class="greeting">
            <h1>Selamat datang, <span id="userName">Hutaw Talaw</span> <i class="fas fa-recycle" style="color:#2e7d64;"></i></h1>
            <p>Terus kelola sampah plastik dan kumpulkan poin</p>
        </div>
        <button id="logoutSideBtn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>

    <!-- Kartu statistik: Poin, Botol, Berat -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span>Total Poin</span>
                <i class="fas fa-star" style="color:#f4b942;"></i>
            </div>
            <div class="stat-value" id="totalPoinVal">9.999</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> 150 Poin dari minggu lalu <span class="trend-up">+12%</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <span>Total Botol</span>
                <i class="fas fa-bottle-water" style="color:#4f9da6;"></i>
            </div>
            <div class="stat-value" id="totalBotolVal">9.999</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> 150 Botol dari minggu lalu</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <span>Total Berat</span>
                <i class="fas fa-weight-hanging" style="color:#5e8b6f;"></i>
            </div>
            <div class="stat-value" id="totalBeratVal">9,999</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> 150 kg dari minggu lalu</div>
        </div>
    </div>

    <!-- GRAFIK PENGELOLAAN FULL WIDTH -->
    <div class="chart-fullwidth">
        <h3><i class="fas fa-chart-line"></i> Grafik Pengelolaan Sampah Botol Plastik</h3>
        <canvas id="wasteChart" width="800" height="300" style="width:100%; max-height:320px;"></canvas>
        <div class="legend-note">
            <span><i class="fas fa-circle" style="color:#2c7da0;"></i> Poin</span>
            <span><i class="fas fa-circle" style="color:#52b788;"></i> Botol</span>
            <span><i class="fas fa-circle" style="color:#e9c46a;"></i> Berat (kg)</span>
        </div>
        <div style="font-size:12px; text-align:center; margin-top:12px; color:#6c8f9a;">
            Periode: 1 Mei - 29 Mei 2026
        </div>
    </div>

    <!-- RIWAYAT TERBARU -->
    <div class="recent-section">
        <h3><i class="fas fa-history"></i> Riwayat Terbaru</h3>
        <div class="table-responsive">
            <table id="historyTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Botol</th>
                        <th>Berat</th>
                        <th>Poin</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="historyBody">
                    <!-- Data riwayat dari JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer motivasi -->
    <div class="motivation-footer">
        <div><i class="fas fa-leaf"></i> Yuk, terus pilah Sampah plastik! Setiap Botol yang kamu kumpulkan membantu bumi menjadi bersih.</div>
        <div><i class="fas fa-hand-holding-heart"></i> #dompetSadhar</div>
    </div>
</main>

<!-- External JavaScript - PASTIKAN dipanggil SETELAH Chart.js -->
<script src="js/wDashboard.js"></script>
</body>
</html>