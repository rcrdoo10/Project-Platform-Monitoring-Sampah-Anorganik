<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class transaksiReward extends Model
{
    protected $table = 'transaksi_reward';
    protected $primaryKey = 'id_transaksi_reward';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_transaksi_reward',
        'jumlah_reward',
        'id_transaksi',
        'id_reward'
    ];
    
    public function transaksi()
    {
        return $this->belongsTo(transaksi::class, 'id_transaksi', 'id_transaksi');
    }
    
    public function reward()
    {
        return $this->belongsTo(reward::class, 'id_reward', 'id_reward');
    }
}