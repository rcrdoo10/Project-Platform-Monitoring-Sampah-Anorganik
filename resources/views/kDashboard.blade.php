@extends('layouts/kLayout') 
<!-- Push untuk ngambil -->
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kDashboard.css') }}">
@endpush
@section('content')

<main class="dashboard-container">
    <div class="header-flex">
        <div class="greeting">
            <h1>Selamat datang, <span id="userName">Admin</span> <i class="fas fa-recycle" style="color:#2e7d64;"></i></h1>
            <p>Kelola dan pantau transaksi penukaran reward</p>
        </div>
        <button id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span>Total Reward</span>
                <i class="fas fa-gift" style="color:#e9c46a;"></i>
            </div>
            <div class="stat-value" id="totalRewardVal">1.250</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> 120 Reward dari bulan lalu <span class="trend-up">+8%</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <span>Transaksi Pending</span>
                <i class="fas fa-clock" style="color:#b6695a;"></i>
            </div>
            <div class="stat-value" id="pendingCount">3</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> Menunggu verifikasi</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <span>Total Transaksi</span>
                <i class="fas fa-exchange-alt" style="color:#52b788;"></i>
            </div>
            <div class="stat-value" id="totalTransaksiVal">2.847</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> 320 Transaksi dari bulan lalu <span class="trend-up">+15%</span></div>
        </div>
    </div>

    <div class="chart-fullwidth">
        <h3><i class="fas fa-chart-line" style="color:#2e7d64;"></i> Grafik Total Penukaran Per Hari</h3>
        <canvas id="wasteChart" width="800" height="300" style="width:100%; max-height:320px;"></canvas>
        <div class="legend-note">
            <span><i class="fas fa-circle" style="color:#2e7d64;"></i> Total Penukaran (Unit)</span>
        </div>
        <div style="font-size:12px; text-align:center; margin-top:12px; color:#5f7c8b;">
            Periode: 1 Mei - 30 Mei 2026
        </div>
    </div>

    <div class="recent-section">
        <div class="recent-header">
            <h3><i class="fas fa-history" style="color:#2e7d64;"></i> Riwayat Penukaran Reward Terbaru</h3>
            <button id="lihatSemuaRiwayatBtn" class="lihat-semua-btn">
                <i class="fas fa-eye"></i> Lihat Semua Riwayat
            </button>
        </div>
        <div class="table-responsive">
            <table id="historyTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Reward</th>
                        <th>Poin</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="historyBody"></tbody>
            </table>
        </div>
    </div>

    <div class="motivation-footer">
        <div><i class="fas fa-leaf"></i> Di balik setiap transaksi ada pengguna yang menanti kepastian. Verifikasi dengan penuh tanggung jawab.</div>
        <div><i class="fas fa-hand-holding-heart"></i> #dompetSadhar</div>
    </div>
</main>

<div id="fullHistoryModal" class="full-history-modal">
    <div class="full-history-content">
        <div class="full-history-header">
            <h3><i class="fas fa-history" style="color:#2e7d64;"></i> Semua Riwayat Penukaran Reward</h3>
            <span class="full-history-close">&times;</span>
        </div>
        <div class="full-history-body">
            <div class="table-responsive">
                <table id="fullHistoryTable">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Reward</th>
                            <th>Poin</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="fullHistoryBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('js/kDashboard.js') }}"></script>
@endpush