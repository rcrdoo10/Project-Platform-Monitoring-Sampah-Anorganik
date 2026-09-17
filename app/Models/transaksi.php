<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_transaksi',
        'poin_transaksi',
        'jumlah_transaksi',
        'tanggal_transaksi',
        'jam_transaksi',
        'status_transaksi',
        'id_user'
    ];

    public function user()
    {
        return $this->belongsTo(akun::class, 'id_user', 'id_user');
    }

    public function rewards()
    {
        return $this->belongsToMany(reward::class, 'transaksi_reward', 'id_transaksi', 'id_reward')
                    ->withPivot('jumlah_reward');
    }
    
    public function transaksiRewards()
    {
        return $this->hasMany(transaksiReward::class, 'id_transaksi', 'id_transaksi');
    }
}