<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () { //rootnya = / , cabang = /login
    return view('login'); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/rewardKopma', function () {
    return view('kRewardKopma'); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/transaksiReward', function () {
    return view('kTransaksiReward'); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/laporan', function () {
    return view('laporan'); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/InputBotol', function () {
    return view('wInputBotol'); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/TukarPoin', function () {
    return view('wTukarPoin'); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});

Route::get('/', function () { 
    return view('wdashboard'); //BUAT SENDIRI SESUAI VIEW YG MAU DIBERI! >1
});
