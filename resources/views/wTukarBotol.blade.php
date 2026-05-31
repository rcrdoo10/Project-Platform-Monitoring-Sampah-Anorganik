@extends('layouts/wLayout')

{{-- Push untuk CSS --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/wTukarBotol.css') }}">
@endpush

@section('content')
<main class="poin-container">
    <div class="header-flex">
        <div class="greeting">
            <h1><i class="fas fa-check-circle" style="color:#2e7d64;"></i> Poin Saya</h1>
            <p>Upload sampah plastik dan kumpulkan poin</p>
        </div>
        <button id="logoutBtn" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>

    <div class="poin-summary">
        <i class="fas fa-star" style="font-size: 3rem; color:#e9c46a;"></i>
        <div class="total-poin-large" id="poinTotalDisplay">9.999</div>
        <div class="poin-level" id="poinLevel">Level: Pejuang Lingkungan</div>
    </div>

    <div class="upload-section">
        <h3><i class="fas fa-cloud-upload-alt" style="color:#2e7d64;"></i> Upload Sampah Plastik</h3>
        <form id="uploadForm">
            <div class="upload-area" id="uploadArea">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Klik atau drag & drop foto disini</p>
                <input type="file" id="imageUpload" accept="image/*" hidden>
            </div>
            <div id="imagePreview" class="image-preview"></div>

            <div class="volume-options">
                <label class="volume-card">
                    <input type="radio" name="volume" value="small" data-botol="10" data-berat="0.15" data-poin="15">
                    <div class="volume-content">
                        <i class="fas fa-bottle-water"></i>
                        <strong>Kecil</strong>
                        <span>330-600 ml</span>
                        <span class="volume-badge">~10 botol/kg</span>
                    </div>
                </label>
                <label class="volume-card">
                    <input type="radio" name="volume" value="medium" data-botol="8" data-berat="0.2" data-poin="20">
                    <div class="volume-content">
                        <i class="fas fa-bottle-water"></i>
                        <strong>Sedang</strong>
                        <span>600-1000 ml</span>
                        <span class="volume-badge">~8 botol/kg</span>
                    </div>
                </label>
                <label class="volume-card">
                    <input type="radio" name="volume" value="large" data-botol="5" data-berat="0.35" data-poin="35">
                    <div class="volume-content">
                        <i class="fas fa-bottle-water"></i>
                        <strong>Besar</strong>
                        <span>1.5-2 L</span>
                        <span class="volume-badge">~5 botol/kg</span>
                    </div>
                </label>
            </div>

            <div class="form-summary" id="formSummary" style="display: none;">
                <h4><i class="fas fa-chart-simple" style="color:#2e7d64;"></i> Ringkasan</h4>
                <div class="summary-details">
                    <span>Perkiraan Botol: <strong id="summaryBotol">0</strong> botol</span>
                    <span>Perkiraan Berat: <strong id="summaryBerat">0</strong> kg</span>
                    <span>Poin Dapat: <strong id="summaryPoin">0</strong> poin</span>
                </div>
            </div>

            <button type="submit" class="submit-upload-btn">
                <i class="fas fa-check-circle"></i> Upload & Verifikasi
            </button>
        </form>
    </div>

    <div class="motivation-footer">
        <div><i class="fas fa-chart-simple"></i> Ayo kumpulkan lebih banyak poin untuk naik level!</div>
        <div><i class="fas fa-medal"></i> Semakin banyak sampah plastik yang didaur ulang, semakin baik untuk bumi!</div>
    </div>
</main>
@endsection

@push('scripts')
    <script src="{{ asset('js/wTukarBotol.js') }}"></script>
@endpush
