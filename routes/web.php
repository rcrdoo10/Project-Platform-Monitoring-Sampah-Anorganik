<?php

use App\Http\Controllers\loginController;
use App\Http\Controllers\PoinController;
use App\Models\akun;
use App\Models\DataSampah;

// ========== ROOT ========== 
Route::get('/', function () {
    return redirect('/login');
});

// ========== LOGIN ==========
Route::get('/login', [loginController::class, 'index'])->name('login');
Route::post('/login', [loginController::class, 'autentikasi']);
Route::post('/logout', [loginController::class, 'logout'])->name('logout');

// ========== API ROUTE ==========
Route::get('/api/get-poin', [PoinController::class, 'getPoin']);
Route::get('/api/get-total-botol', [PoinController::class, 'getTotalBotol']);
Route::get('/api/get-total-berat', [PoinController::class, 'getTotalBerat']);
Route::get('/api/get-all-stats', [PoinController::class, 'getAllStats']);
Route::get('/api/get-history', [PoinController::class, 'getHistory']);
Route::get('/api/get-all-history', [PoinController::class, 'getAllHistory']);
Route::get('/api/chart-stats', [PoinController::class, 'getChartStats']);
// ========== API SAVE TRANSAKSI ==========
Route::post('/api/save-transaksi', [PoinController::class, 'saveTransaksi']);
Route::post('/api/update-poin', [PoinController::class, 'updatePoin']);
Route::get('/api/laporan/stats', [App\Http\Controllers\grafikLaporanController::class, 'getStats']);
Route::get('/api/laporan/chart', [App\Http\Controllers\grafikLaporanController::class, 'getChartData']);

// Tambahkan di bagian API ROUTE atau setelah route kDashboard
Route::get('/api/reward/dashboard-data', [App\Http\Controllers\rewardController::class, 'getDashboardData']);
Route::get('/api/rewards', [App\Http\Controllers\rewardController::class, 'index']);
Route::get('/api/rewards/available', [App\Http\Controllers\rewardController::class, 'getAvailableRewards']);

// ========== DASHBOARD WARGA ==========
Route::get('/wDashboard', function () {
    if (!session()->has('user_id')) {
        return redirect('/login');
    }
    if (session('user_role') != 'warga') {
        return redirect('/login');
    }
    
    $userId = session('user_id');
    
    $user = akun::where('id_user', $userId)->first();
    
    if (!$user) {
        session()->flush();
        return redirect('/login');
    }
    
    $totalBotol = DataSampah::where('id_user', $userId)
        ->where('status_scan', 'success')
        ->sum('jumlah_botol');
    
    $totalBerat = DataSampah::where('id_user', $userId)
        ->where('status_scan', 'success')
        ->sum('berat_botol');
    
    session([
        'user_poin' => $user->total_poin ?? 0,
        'user_nama' => $user->nama,
    ]);
    
    return view('wDashboard', [
        'title' => 'Dashboard',
        'user_nama' => $user->nama ?? 'User',
        'user_poin' => $user->total_poin ?? 0,
        'totalBotol' => $totalBotol ?? 0,
        'totalBerat' => $totalBerat ?? 0
    ]);
});

// ========== TUKAR BOTOL ==========
Route::get('/wTukarBotol', function () {
    if (!session()->has('user_id')) {
        return redirect('/login');
    }
    if (session('user_role') != 'warga') {
        return redirect('/login');
    }
    
    $userId = session('user_id');
    $user = akun::where('id_user', $userId)->first();
    
    // Update session dengan poin terbaru
    if ($user) {
        session(['user_poin' => $user->total_poin ?? 0]);
    }
    
    return view('wTukarBotol', [
        'title' => 'Tukar Botol',
        'user_poin' => session('user_poin', 0),
        'user_nama' => session('user_nama', 'User')
    ]);
});

// ========== REWARD ==========
Route::get('/Reward', function () {
    if (!session()->has('user_id')) {
        return redirect('/login');
    }
    if (session('user_role') != 'warga') {
        return redirect('/login');
    }
    
    return view('wReward', [
        'title' => 'Reward'
    ]);
});

// ========== DASHBOARD KOPMA ==========
Route::get('/kDashboard', function () {
    if (!session()->has('user_id')) {
        return redirect('/login');
    }
    if (session('user_role') != 'kopma') {
        return redirect('/login');
    }
    return view('kDashboard', [
        'title' => 'Dashboard'
    ]);
});

// ========== LAPORAN ==========
Route::get('/laporan', function () {
    if (!session()->has('user_id')) {
        return redirect('/login');
    }
    $allowedRoles = ['petugas', 'psl', 'rektorat'];
    if (!in_array(session('user_role'), $allowedRoles)) {
        return redirect('/login');
    }
    
    // Untuk role petugas, ambil SEMUA data (tanpa filter id_user)
    $totalBotol = App\Models\DataSampah::where('status_scan', 'success')->sum('jumlah_botol');
    $totalBerat = App\Models\DataSampah::where('status_scan', 'success')->sum('berat_botol');
    
    return view('laporan', [
        'title' => 'Laporan',
        'totalBotol' => $totalBotol ?? 0,
        'totalBerat' => $totalBerat ?? 0,
        'user_nama' => session('user_nama', 'Petugas')
    ]);
});