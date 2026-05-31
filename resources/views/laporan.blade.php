<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Dompet Sadhar - Dashboard Sampah Plastik</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="css/laporan.css">
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
    </ul>
</nav>
<div class="overlay" id="overlay"></div>

<div id="dashboardPage" class="page-content active-page">
    <main class="dashboard-container">
        <div class="header-flex">
            <div class="greeting">
                <h1>Selamat datang, <span id="userName">Ray Ricardo</span> <i class="fas fa-recycle" style="color:#2e7d64;"></i></h1>
                <p>Terus kelola sampah plastik dan kelola lingkungan</p>
            </div>
            <button id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span>Total Botol</span>
                    <i class="fas fa-bottle-water" style="color:#52b788;"></i>
                </div>
                <div class="stat-value" id="totalBotolVal">9.999</div>
                <div class="stat-sub"><i class="fas fa-chart-line"></i> 150 Botol dari minggu lalu <span class="trend-up">+12%</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span>Total Berat</span>
                    <i class="fas fa-weight-hanging" style="color:#e9c46a;"></i>
                </div>
                <div class="stat-value" id="totalBeratVal">9,999</div>
                <div class="stat-sub"><i class="fas fa-chart-line"></i> 150 kg dari minggu lalu</div>
            </div>
        </div>

        <div class="chart-fullwidth" id="reportSection">
            <div class="chart-filter">
                <div class="filter-buttons">
                    <button class="filter-btn active" data-period="weekly">📊 Mingguan</button>
                    <button class="filter-btn" data-period="monthly">📅 Bulanan</button>
                    <button class="filter-btn" data-period="yearly">📈 Tahunan</button>
                </div>
                <button id="downloadReportBtn" class="download-btn">
                    <i class="fas fa-download"></i> Unduh Laporan
                </button>
            </div>
            <h3><i class="fas fa-chart-line" style="color:#2e7d64;"></i> Grafik Pengelolaan Sampah Botol Plastik</h3>
            <canvas id="wasteChart" width="800" height="300" style="width:100%; max-height:320px;"></canvas>
            <div class="legend-note">
                <span><i class="fas fa-circle" style="color:#52b788;"></i> Botol</span>
                <span><i class="fas fa-circle" style="color:#e9c46a;"></i> Berat (kg)</span>
            </div>
            <div class="period-label" id="periodLabel">
                Periode: Mingguan (1-7 Mei, 8-14 Mei, 15-21 Mei, 22-28 Mei 2026)
            </div>
        </div>

        <div class="motivation-footer">
            <div><i class="fas fa-leaf"></i>Pantau dan evaluasi laporan pengelolaan sampah plastik Anda. Dengan data yang akurat, kita bisa menciptakan perubahan nyata bagi lingkungan.</div>
            <div><i class="fas fa-hand-holding-heart"></i> #dompetSadhar</div>
        </div>
    </main>
</div>

<script src="js/laporan.js"></script>
</body>
</html>
