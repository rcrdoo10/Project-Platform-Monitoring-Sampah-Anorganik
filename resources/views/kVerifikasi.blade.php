@extends('layouts/kLayout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kVerifikasi.css') }}">
@endpush

@section('content')
<main class="verifikasi-container">
    <div class="header-flex">
        <div class="greeting">
            <h1><i class="fas fa-check-double" style="color:#2e7d64;"></i> Verifikasi Transaksi</h1>
            <p>Kelola dan verifikasi transaksi penukaran reward oleh warga</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>

    <div class="filter-tabs">
        <button class="tab-btn active" data-status="pending">⏳ Pending <span class="count-badge" id="pendingCount">0</span></button>
        <button class="tab-btn" data-status="approved">✅ Disetujui <span class="count-badge" id="approvedCount">0</span></button>
    </div>

    <div class="verifikasi-table">
        <h3><i class="fas fa-list" style="color:#2e7d64;"></i> <span id="tableTitle">Transaksi Menunggu Verifikasi</span></h3>
        <div class="table-responsive">
            <table id="verifikasiTable">
                <thead>
                    <tr>
                        <th style="text-align:center;">Tanggal & Jam</th>
                        <th style="text-align:center;">ID User</th>
                        <th style="text-align:center;">Nama Warga</th>
                        <th style="text-align:center;">Jumlah Transaksi</th>
                        <th style="text-align:center;">Total Poin</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="verifikasiBody">
                    <tr>
                        <td colspan="7" style="text-align:center;">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="motivation-footer">
        <div><i class="fas fa-shield-alt"></i> Verifikasi dengan teliti sebelum menyetujui transaksi warga.</div>
        <div><i class="fas fa-hand-holding-heart"></i> #dompetSadhar</div>
    </div>
</main>

<!-- Modal Detail Transaksi -->
<div id="detailTransaksiModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fas fa-receipt"></i> Detail Transaksi</h3>
            <span class="modal-close-detail">&times;</span>
        </div>
        <div class="modal-body">
            <p><strong>ID Transaksi:</strong> <span id="detailId"></span></p>
            <p><strong>ID User:</strong> <span id="detailIdUser"></span></p>
            <p><strong>Nama Warga:</strong> <span id="detailNama"></span></p>
            <p><strong>Tanggal:</strong> <span id="detailTanggal"></span></p>
            <p><strong>Jam:</strong> <span id="detailJam"></span></p>
            <p><strong>Jumlah Transaksi:</strong> <span id="detailJumlah"></span></p>
            <p><strong>Total Poin:</strong> <span id="detailPoin"></span></p>
            <p><strong>Status:</strong> <span id="detailStatus"></span></p>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel close-detail-btn">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/kVerifikasi.js') }}"></script>
@endpush