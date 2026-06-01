<?php

use App\Http\Controllers\loginController;
use App\Http\Controllers\PoinController;
use App\Http\Controllers\rewardController;
use App\Models\akun;
use App\Models\DataSampah;
use App\Models\Reward;

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
Route::post('/api/save-transaksi', [PoinController::class, 'saveTransaksi']);
Route::post('/api/update-poin', [PoinController::class, 'updatePoin']);
Route::get('/api/laporan/stats', [App\Http\Controllers\grafikLaporanController::class, 'getStats']);
Route::get('/api/laporan/chart', [App\Http\Controllers\grafikLaporanController::class, 'getChartData']);

// ========== ROUTE REWARD ==========
// ========== ROUTE REWARD CRUD ==========
Route::get('/api/total-stok-reward', [rewardController::class, 'getTotalStok']);
Route::get('/api/all-rewards', [rewardController::class, 'getAllRewards']);
Route::post('/api/rewards', [rewardController::class, 'store']);
Route::put('/api/rewards/{id}', [rewardController::class, 'update']);
Route::delete('/api/rewards/{id}', [rewardController::class, 'destroy']);

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
    
    $userId = session('user_id');
    $user = App\Models\akun::where('id_user', $userId)->first();
    $totalStok = App\Models\Reward::sum('stok');
    
    return view('kDashboard', [
        'title' => 'Dashboard',
        'totalStok' => $totalStok,
        'userName' => $user->nama ?? 'Admin'
    ]);
});

// ========== VERIFIKASI KOPMA ==========
Route::get('/kVerifikasi', function () {
    if (!session()->has('user_id')) {
        return redirect('/login');
    }
    if (session('user_role') != 'kopma') {
        return redirect('/login');
    }
    
    $userId = session('user_id');
    $user = App\Models\akun::where('id_user', $userId)->first();
    
    return view('kVerifikasi', [
        'title' => 'Verifikasi',
        'userName' => $user->nama ?? 'Admin'
    ]);
});

// ========== REWARD KOPMA ==========
Route::get('/kReward', function () {
    if (!session()->has('user_id')) {
        return redirect('/login');
    }
    if (session('user_role') != 'kopma') {
        return redirect('/login');
    }
    
    $userId = session('user_id');
    $user = App\Models\akun::where('id_user', $userId)->first();
    $rewards = App\Models\Reward::all(); // Ambil semua reward
    
    return view('kReward', [
        'title' => 'Reward',
        'userName' => $user->nama ?? 'Admin',
        'rewards' => $rewards
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
    
    $totalBotol = DataSampah::where('status_scan', 'success')->sum('jumlah_botol');
    $totalBerat = DataSampah::where('status_scan', 'success')->sum('berat_botol');
    
    return view('laporan', [
        'title' => 'Laporan',
        'totalBotol' => $totalBotol ?? 0,
        'totalBerat' => $totalBerat ?? 0,
        'user_nama' => session('user_nama', 'Petugas')
    ]);
});