@extends('layouts/kLayout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kReward.css') }}">
@endpush

@section('content')
<main class="reward-container">
    <div class="header-flex">
        <div class="greeting">
            <h1><i class="fas fa-gift" style="color:#2e7d64;"></i> Kelola Reward</h1>
            <p>Kelola daftar reward, kategori, harga (poin), dan stok reward</p>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>

    <div class="filter-category">
        <button class="category-btn active" data-category="all">📦 Semua</button>
        <button class="category-btn" data-category="makanan">🍔 Makanan</button>
        <button class="category-btn" data-category="minuman">🥤 Minuman</button>
        <button class="category-btn" data-category="barang">🎁 Barang</button>
    </div>

    <div class="reward-header">
        <h3><i class="fas fa-tags" style="color:#2e7d64;"></i> Daftar Reward</h3>
        <button id="addRewardBtn" class="add-reward-btn" type="button">
            <i class="fas fa-plus"></i> Tambah Reward
        </button>
    </div>

    <div class="reward-list">
        <div id="rewardItemsContainer"></div>
    </div>

    <div class="motivation-footer">
        <div><i class="fas fa-edit"></i> Klik tombol Edit untuk mengubah kategori, harga (poin) dan jumlah stok reward.</div>
        <div><i class="fas fa-hand-holding-heart"></i> #dompetSadhar</div>
    </div>
</main>

<div id="editRewardModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Reward</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editRewardForm">
                <input type="hidden" id="editRewardId">
                <div class="form-group">
                    <label>Nama Reward</label>
                    <input type="text" id="editRewardName" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <input type="text" id="editRewardDesc" placeholder="Deskripsi reward">
                </div>
                <div class="form-group">
                    <label>Jenis Reward</label>
                    <select id="editRewardCategory">
                        <option value="Makanan">🍔 Makanan</option>
                        <option value="Minuman">🥤 Minuman</option>
                        <option value="Barang">🎁 Barang</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Satuan</label>
                    <select id="editRewardSatuan">
                        <option value="pcs">PCS (Per Potong)</option>
                        <option value="lusin">Lusin (12 pcs)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Harga (Poin yang dibutuhkan)</label>
                    <input type="number" id="editRewardPoints" min="0" required>
                </div>
                <div class="form-group">
                    <label>Jumlah Stok Reward</label>
                    <input type="number" id="editRewardStock" min="0" step="1" required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" id="cancelEditBtn">Batal</button>
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="addRewardModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Tambah Reward Baru</h3>
            <span class="modal-close-add">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addRewardForm">
                <div class="form-group">
                    <label>Nama Reward</label>
                    <input type="text" id="addRewardName" placeholder="Contoh: Paket Nasi Box" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <input type="text" id="addRewardDesc" placeholder="Deskripsi reward">
                </div>
                <div class="form-group">
                    <label>Jenis Reward</label>
                    <select id="addRewardCategory">
                        <option value="Makanan">🍔 Makanan</option>
                        <option value="Minuman">🥤 Minuman</option>
                        <option value="Barang">🎁 Barang</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Satuan</label>
                    <select id="addRewardSatuan">
                        <option value="pcs">PCS (Per Potong)</option>
                        <option value="lusin">Lusin (12 pcs)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Harga (Poin yang dibutuhkan)</label>
                    <input type="number" id="addRewardPoints" min="0" value="500" required>
                </div>
                <div class="form-group">
                    <label>Jumlah Stok Reward</label>
                    <input type="number" id="addRewardStock" min="0" step="1" value="10" required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" id="cancelAddBtn">Batal</button>
                    <button type="submit" class="btn-save">Tambah Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/kReward.js') }}"></script>
@endpush