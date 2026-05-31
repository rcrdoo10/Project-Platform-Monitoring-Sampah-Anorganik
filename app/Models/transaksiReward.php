<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiReward extends Model
{
    protected $table = 'transaksi_reward';
    protected $primaryKey = 'id_transaksi_reward';
    public $timestamps = false;
    
    protected $fillable = [
        'id_transaksi_reward',
        'jumlah_reward',
        'id_transaksi',
        'id_reward'
    ];
}