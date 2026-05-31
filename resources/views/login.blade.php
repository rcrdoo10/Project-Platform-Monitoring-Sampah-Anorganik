<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Dompet Sadhar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <div class="login-brand">
            <div>
                <div class="logo">
                    <i class="fas fa-recycle"></i>
                    <span>Dompet Sadhar</span>
                </div>
                <div class="brand-title">
                    Tukar Sampah Plastik<br>Menjadi Poin Berharga
                </div>
                <div class="brand-desc">
                    Platform digital yang membantu Anda mengelola sampah plastik 
                    dan menukarkannya dengan berbagai reward menarik.
                </div>
            </div>
            <ul class="features">
                <li><i class="fas fa-check-circle"></i> Kumpulkan poin dari sampah plastik</li>
                <li><i class="fas fa-check-circle"></i> Tukarkan dengan reward menarik</li>
                <li><i class="fas fa-check-circle"></i> Pantau statistik dan riwayat transaksi</li>
                <li><i class="fas fa-check-circle"></i> Dukung lingkungan dengan aksi nyata</li>
            </ul>
        </div>

        <div class="login-form">
            <div class="form-header">
                <h2>Selamat Datang!</h2>
                <p>Masuk untuk melanjutkan ke akun Anda</p>
            </div>

            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="input-group">
                    <label>ID User</label>
                    <div class="input-icon">
                        <i class="fas fa-id-card"></i>
                        <input type="text" name="id_user" placeholder="Masukkan ID User" value="{{ old('id_user') }}" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <i class="fas fa-lock lock-icon"></i>
                        <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </button>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>