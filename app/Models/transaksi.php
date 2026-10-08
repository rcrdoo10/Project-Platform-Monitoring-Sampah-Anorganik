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
        'tanggal_transaksi',
        'jam_transaksi',          // ✅ tambah
        'status_transaksi',
        'id_user',
    ];

    public function user()
    {
        return $this->belongsTo(akun::class, 'id_user', 'id_user');
    }

    public function menukarkans()
    {
        return $this->hasMany(menukarkan::class, 'id_transaksi', 'id_transaksi');
    }

    public function rewards()
    {
        return $this->belongsToMany(
            reward::class,
            'menukarkan',
            'id_transaksi',
            'id_reward'
        )->withPivot('jumlah_reward');
    }
}