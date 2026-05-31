<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\loginController; 

Route::get('/# Login ke MySQL
mysql -u root -p

# Tekan Enter (karena password kosong di XAMPP default)', [loginController::class, 'index'])->name('login');
Route::post('/login', [loginController::class, 'autentikasi']);
Route::post('/logout', [loginController::class, 'logout'])->name('logout');//rootnya = / , cabang = /login
 //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1


Route::get('/kDashboard', function () {
    return view('kDashboard', [
        "title" => "Dashboard"
    ]); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/kReward', function () {
    return view('kReward', [
        "title" => "Reward"
    ]); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/kVerifikasi', function () {
    return view('kVerifikasi', [
        "title" => "Verifikasi"
    ]); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/laporan', function () {
    return view('laporan', [
        "title" => "Laporan"
    ]); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/TukarBotol', function () {
    return view('wTukarBotol', [
        "title" => "Tukar Botol"
    ]); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/Reward', function () {
    return view('wReward', [
        "title" => "Reward"
    ]); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/dashboard', function () { 
    return view('wdashboard', [
        "title" => "Dashboard"
    ]); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});
