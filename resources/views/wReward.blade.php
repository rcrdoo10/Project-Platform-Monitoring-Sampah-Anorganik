@extends('layouts/wLayout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/wReward.css') }}">
@endpush

@section('content')

<script>
    // Data dari session Laravel
    window.userData = {
        poin: {{ session('user_poin', 0) }},
        nama: "{{ session('user_nama', 'User') }}",
        id: "{{ session('user_id') }}",
        role: "{{ session('user_role') }}"
    };
    
    // Data reward dari controller
    window.rewardsData = @json($rewards ?? []);
    
    console.log('User Data:', window.userData);
    console.log('Rewards Data:', window.rewardsData);
</script>

<main class="reward-container">
    <div class="header-flex">
        <div class="greeting">
            <h1><i class="fas fa-gift" style="color:#2e7d64;"></i> Tukar Poin</h1>
            <p>Tukarkan poin Anda dengan hadiah menarik</p>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>

    <div class="poin-summary" style="background: linear-gradient(135deg, #2e7d64, #1e5a48);">
        <i class="fas fa-coins" style="font-size: 3rem; color:#e9c46a;"></i>
        <div class="total-poin-large" id="rewardPoinDisplay">{{ number_format(session('user_poin', 0), 0, ',', '.') }}</div>
        <div class="poin-level">Poin tersedia untuk ditukar</div>
    </div>

    <div class="filter-category">
        <button class="category-btn active" data-category="all">📦 Semua</button>
        <button class="category-btn" data-category="food">🍔 Makanan</button>
        <button class="category-btn" data-category="drink">🥤 Minuman</button>
        <button class="category-btn" data-category="goods">🎁 Barang</button>
    </div>

    <div class="reward-list">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-tags" style="color:#2e7d64;"></i> Daftar Hadiah</h3>
        <div id="rewardItemsContainer"></div>
    </div>

    <!-- ========== RIWAYAT PENUKARAN DENGAN TOMBOL LIHAT SEMUA ========== -->
    <div class="history-exchange-section">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h3><i class="fas fa-history" style="color:#2e7d64;"></i> Riwayat Penukaran Reward</h3>
            <button id="lihatSemuaHistoryBtn" class="lihat-semua-btn" style="background: #2e7d64; color: white; border: none; padding: 8px 20px; border-radius: 40px; cursor: pointer; font-weight: 500; transition: 0.2s;">
                <i class="fas fa-eye"></i> Lihat Semua Riwayat
            </button>
        </div>
        
        <!-- Filter status -->
        <div class="filter-status">
            <button class="status-filter-btn active" data-filter="all">Semua</button>
            <button class="status-filter-btn" data-filter="pending">⏳ Pending</button>
            <button class="status-filter-btn" data-filter="approved">✅ Disetujui</button>
        </div>
        
        <div id="historyLoadingIndicator" style="display: none; text-align: center; padding: 20px;">
            <i class="fas fa-spinner fa-spin"></i> Memuat data...
        </div>
        
        <div class="table-responsive">
            <table id="exchangeHistoryTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Reward</th>
                        <th>Jumlah</th>
                        <th>Total Poin</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="exchangeHistoryBody">
                    <tr>
                        <td colspan="5" style="text-align:center;">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="motivation-footer">
        <div><i class="fas fa-shopping-cart"></i> Pilih jumlah reward yang ingin ditukarkan!</div>
        <div><i class="fas fa-tree"></i> Setiap penukaran membantu lingkungan</div>
    </div>
</main>

<!-- Modal Konfirmasi -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-question-circle"></i> Konfirmasi Penukaran</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <p id="confirmMessage"></p>
            <div class="modal-buttons">
                <button class="btn-cancel" id="cancelConfirmBtn">Batal</button>
                <button class="btn-confirm" id="confirmExchangeBtn">Ya, Tukar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Riwayat Lengkap -->
<div id="fullHistoryModal" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3><i class="fas fa-history"></i> Semua Riwayat Penukaran</h3>
            <span class="modal-close" id="closeFullHistoryModal">&times;</span>
        </div>
        <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
            <div id="fullHistoryContent"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/wReward.js') }}"></script>
@endpush