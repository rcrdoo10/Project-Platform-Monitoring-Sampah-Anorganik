@extends('layouts/wLayout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/wDashboard.css') }}">
@endpush

@section('content')

<script>
    // Data dari route
    window.initialData = {
        user_nama: "{{ $user_nama }}",
        user_poin: {{ $user_poin }},
        total_botol: {{ $totalBotol }},
        total_berat: {{ $totalBerat }}
    };
</script>

<main class="dashboard-container">
    <div class="header-flex">
        <div class="greeting">
            <h1>Selamat datang, <span id="userName">{{ $user_nama }}</span> <i class="fas fa-recycle" style="color:#2e7d64;"></i></h1>
            <p>Terus kelola sampah plastik dan kumpulkan poin</p>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span>Total Poin</span>
                <i class="fas fa-star" style="color:#e9c46a;"></i>
            </div>
            <div class="stat-value" id="totalPoinVal">{{ number_format($user_poin, 0, ',', '.') }}</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> Poin Anda saat ini</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <span>Total Botol</span>
                <i class="fas fa-bottle-water" style="color:#52b788;"></i>
            </div>
            <div class="stat-value" id="totalBotolVal">{{ number_format($totalBotol, 0, ',', '.') }}</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> Total botol yang discan</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <span>Total Berat</span>
                <i class="fas fa-weight-hanging" style="color:#e9c46a;"></i>
            </div>
            <div class="stat-value" id="totalBeratVal">{{ number_format($totalBerat, 0, ',', '.') }} kg</div>
            <div class="stat-sub"><i class="fas fa-chart-line"></i> Total berat yang discan</div>
        </div>
    </div>

    <div class="chart-fullwidth">
        <h3><i class="fas fa-chart-line" style="color:#2e7d64;"></i> Grafik Pengelolaan Sampah Botol Plastik</h3>
        <canvas id="wasteChart" width="800" height="300" style="width:100%; max-height:320px;"></canvas>
        <div class="legend-note">
            <span><i class="fas fa-circle" style="color:#2e7d64;"></i> Poin</span>
            <span><i class="fas fa-circle" style="color:#52b788;"></i> Botol</span>
            <span><i class="fas fa-circle" style="color:#e9c46a;"></i> Berat (kg)</span>
        </div>
        <div style="font-size:12px; text-align:center; margin-top:12px; color:#5f7c8b;">
            Periode: 30 Hari Terakhir
        </div>
    </div>

    <div class="recent-section">
        <div class="recent-header">
            <h3><i class="fas fa-history" style="color:#2e7d64;"></i> Riwayat Terbaru</h3>
            <button id="lihatSemuaRiwayatBtn" class="lihat-semua-btn">
                <i class="fas fa-eye"></i> Lihat Semua Riwayat
            </button>
        </div>
        <div class="table-responsive">
            <table id="historyTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Botol</th>
                        <th>Berat</th>
                        <th>Poin</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="historyBody"></tbody>
            </table>
        </div>
    </div>

    <div class="motivation-footer">
        <div><i class="fas fa-leaf"></i> Yuk, terus pilah Sampah plastik! Setiap Botol yang kamu kumpulkan membantu bumi menjadi bersih.</div>
        <div><i class="fas fa-hand-holding-heart"></i> #dompetSadhar</div>
    </div>
</main>

<!-- Modal Riwayat Lengkap -->
<div id="fullHistoryModal" class="full-history-modal">
    <div class="full-history-content">
        <div class="full-history-header">
            <h3><i class="fas fa-history" style="color:#2e7d64;"></i> Semua Riwayat Transaksi</h3>
            <span class="full-history-close">&times;</span>
        </div>
        <div class="full-history-body">
            <div class="table-responsive">
                <table id="fullHistoryTable">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Botol</th>
                            <th>Berat</th>
                            <th>Poin</th>
                            <th>Status</th>
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
    <script src="{{ asset('js/wDashboard.js') }}"></script>
@endpush