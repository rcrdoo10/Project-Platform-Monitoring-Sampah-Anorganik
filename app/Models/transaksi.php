<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    public $timestamps = false;
    
    protected $fillable = [
        'id_transaksi',
        'poin_transaksi',
        'tanggal_transaksi',
        'status_transaksi',
        'id_user'
    ];
}