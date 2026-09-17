<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reward extends Model
{
    protected $table = 'reward';
    protected $primaryKey = 'id_reward';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nama_reward',
        'jenis_reward',
        'stok',
        'poin_dibutuhkan',
        'gambar_reward',
        'deskripsi',
        'kategori'
    ];

    // Relasi ke transaksi melalui transaksiReward
    public function transaksis()
    {
        return $this->belongsToMany(transaksi::class, 'transaksi_reward', 'id_reward', 'id_transaksi')
                    ->withPivot('jumlah_reward');
    }
    
    // Relasi ke transaksiReward
    public function transaksiRewards()
    {
        return $this->hasMany(transaksiReward::class, 'id_reward', 'id_reward');
    }
}