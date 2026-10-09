<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\loginController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\rewardController;
use App\Http\Controllers\poinController;
use App\Http\Controllers\transaksiController;
use App\Http\Controllers\grafikLaporanController;

Route::get('/login', [loginController::class, 'index'])->name('login');
Route::post('/login', [loginController::class, 'autentikasi']);
Route::post('/logout', [loginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    if (session('user_id')) {
        $role = session('user_role');
        if ($role == 'warga') return redirect('/wDashboard');
        if ($role == 'kopma') return redirect('/kDashboard');
        if ($role == 'petugas' || $role == 'psl' || $role == 'rektorat') return redirect('/laporan');
        return redirect('/login');
    }
    return redirect('/login');
});

Route::get('/wDashboard', [dashboardController::class, 'warga'])->name('wDashboard');
Route::get('/wTukarBotol', [dashboardController::class, 'wTukarBotol'])->name('wTukarBotol');
Route::get('/wReward', [dashboardController::class, 'wReward'])->name('wReward');
Route::get('/kDashboard', [dashboardController::class, 'kopma'])->name('kDashboard');
Route::get('/kVerifikasi', [dashboardController::class, 'verifikasi'])->name('kVerifikasi');
Route::get('/kReward', [rewardController::class, 'index'])->name('kReward');
Route::get('/laporan', [dashboardController::class, 'laporan'])->name('laporan');

Route::prefix('api')->group(function () {
    Route::get('/poin', [poinController::class, 'getPoin']);
    Route::get('/stats', [poinController::class, 'getAllStats']);
    Route::get('/history', [poinController::class, 'getHistory']);
    Route::post('/transaksi', [poinController::class, 'saveTransaksi']);
    Route::get('/history/all', [poinController::class, 'getAllHistory']);
    Route::get('/history/reward/all', [poinController::class, 'getAllHistoryReward']);
    Route::get('/chart', [grafikLaporanController::class, 'getChartData']);
    Route::get('/laporan/chart', [grafikLaporanController::class, 'getChartData']);
    Route::get('/laporan/stats', [grafikLaporanController::class, 'getStats']);
    Route::get('/laporan/export-docx', [grafikLaporanController::class, 'exportDocx'])->name('laporan.export.docx');
    Route::get('/warga/chart-stats', [poinController::class, 'getChartStats']);
    Route::get('/reward/all', [rewardController::class, 'getAllRewards']);
    Route::get('/reward/stok', [rewardController::class, 'getTotalStok']);
    Route::post('/exchange-reward', [rewardController::class, 'exchangeReward']);
    Route::get('/user/exchange-history', [rewardController::class, 'getUserExchangeHistory']);

    // ========== CRUD REWARD (upload gambar) ==========
    Route::post('/rewards', [rewardController::class, 'store']);
    Route::put('/rewards/{id}', [rewardController::class, 'update']);
    Route::post('/rewards/{id}', [rewardController::class, 'update']);   // ✅ untuk FormData (update dengan file)
    Route::delete('/rewards/{id}', [rewardController::class, 'destroy']);

    // ========== TRANSAKSI ==========
    Route::get('/transaksi/pending', [transaksiController::class, 'getPending']);
    Route::get('/transaksi/approved', [transaksiController::class, 'getApproved']);
    Route::get('/transaksi/all', [transaksiController::class, 'getAll']);
    Route::post('/transaksi/approve/{id}', [transaksiController::class, 'approve']);
    Route::put('/transaksi/{id}/approve', [transaksiController::class, 'approve']);
});