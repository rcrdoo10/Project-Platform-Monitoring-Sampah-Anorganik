@extends('layouts/kLayout') 
<!-- Push untuk ngambil -->
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kVerifikasi.css') }}">
@endpush
@section('content')

<main class="verifikasi-container">
    <div class="header-flex">
        <div class="greeting">
            <h1><i class="fas fa-check-double" style="color:#2e7d64;"></i> Verifikasi Transaksi</h1>
            <p>Kelola dan verifikasi transaksi penukaran reward</p>
        </div>
        <button id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>

    <div class="filter-tabs">
        <button class="tab-btn active" data-status="pending">⏳ Pending</button>
        <button class="tab-btn" data-status="approved">✅ Disetujui</button>
    </div>

    <div class="verifikasi-table">
        <h3><i class="fas fa-list" style="color:#2e7d64;"></i> <span id="tableTitle">Transaksi Menunggu Verifikasi</span></h3>
        <div class="table-responsive">
            <table id="verifikasiTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Pengguna</th>
                        <th>Reward</th>
                        <th>Poin</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="verifikasiBody"></tbody>
            </table>
        </div>
    </div>

    <div class="motivation-footer">
        <div><i class="fas fa-shield-alt"></i> Verifikasi dengan teliti sebelum menyetujui transaksi.</div>
        <div><i class="fas fa-hand-holding-heart"></i> #dompetSadhar</div>
    </div>
</main>

@endsection
@push('scripts')
    <script src="{{ asset('js/kVerifikasi.js') }}"></script>
@endpush