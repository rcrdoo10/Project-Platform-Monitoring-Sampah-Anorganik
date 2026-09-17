@extends('layouts/kLayout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kDashboard.css') }}">
@endpush

@section('content')
<main class="dashboard-container">
    <div class="header-flex">
        <div class="greeting">
            <h1>Selamat datang, <span id="userName">{{ session('user_nama') ?? 'Admin' }}</span> <i class="fas fa-recycle" style="color:#2e7d64;"></i></h1>
            <p>Kelola dan pantau transaksi penukaran reward</p>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span>Total Reward</span>
                <i class="fas fa-gift" style="color:#e9c46a;"></i>
            </div>
            <div class="stat-value">{{ number_format($totalReward ?? 0) }}</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> Total stok reward tersedia</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <span>Transaksi Pending</span>
                <i class="fas fa-clock" style="color:#b6695a;"></i>
            </div>
            <div class="stat-value">{{ number_format($transaksiPending ?? 0) }}</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> Menunggu verifikasi</div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <span>Total Transaksi</span>
                <i class="fas fa-exchange-alt" style="color:#52b788;"></i>
            </div>
            <div class="stat-value">{{ number_format($totalTransaksi ?? 0) }}</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> Total transaksi selesai</div>
        </div>
    </div>

    <!-- Filter Periode -->
    <div class="filter-period">
        <button class="period-btn {{ ($currentPeriod ?? 'daily') == 'daily' ? 'active' : '' }}" data-period="daily">
            <i class="fas fa-calendar-day"></i> Harian
        </button>
        <button class="period-btn {{ ($currentPeriod ?? 'daily') == 'weekly' ? 'active' : '' }}" data-period="weekly">
            <i class="fas fa-calendar-week"></i> Mingguan
        </button>
        <button class="period-btn {{ ($currentPeriod ?? 'daily') == 'monthly' ? 'active' : '' }}" data-period="monthly">
            <i class="fas fa-calendar-alt"></i> Bulanan
        </button>
    </div>

    <div class="chart-fullwidth">
        <h3><i class="fas fa-chart-line" style="color:#2e7d64;"></i> Grafik Total Penukaran</h3>
        <canvas id="wasteChart" width="800" height="300" style="width:100%; max-height:320px;"></canvas>
        <div class="legend-note">
            <span><i class="fas fa-circle" style="color:#2e7d64;"></i> Total Penukaran (Unit)</span>
        </div>
        <div style="font-size:12px; text-align:center; margin-top:12px; color:#5f7c8b;" id="periodText">
            Periode: 7 Hari Terakhir
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
            <h3><i class="fas fa-history"></i> Semua Riwayat Penukaran Reward</h3>
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

<div id="detailTransaksiModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fas fa-receipt"></i> Detail Transaksi</h3>
            <span class="modal-close-detail">&times;</span>
        </div>
        <div class="modal-body">
            <p><strong>ID Transaksi:</strong> <span id="detailId"></span></p>
            <p><strong>Tanggal:</strong> <span id="detailTanggal"></span></p>
            <p><strong>Nama Pengguna:</strong> <span id="detailNama"></span></p>
            <p><strong>Reward:</strong> <span id="detailReward"></span></p>
            <p><strong>Poin:</strong> <span id="detailPoin"></span></p>
            <p><strong>Status:</strong> <span id="detailStatus"></span></p>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel close-detail-btn">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Data dari controller
    window.chartLabels = @json($chartLabels ?? []);
    window.chartData = @json($chartData ?? []);
    window.recentTransactions = @json($recentTransactions ?? []);
    window.allTransactions = @json($allTransactions ?? []);
    window.currentPeriod = @json($currentPeriod ?? 'daily');
</script>
<script src="{{ asset('js/kDashboard.js') }}"></script>
@endpush