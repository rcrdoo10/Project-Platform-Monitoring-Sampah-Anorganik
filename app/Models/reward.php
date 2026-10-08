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
        'gambar_reward',        // ✅ WAJIB ADA
        'deskripsi',
        'kategori',
    ];

    public function menukarkans()
    {
        return $this->hasMany(menukarkan::class, 'id_reward', 'id_reward');
    }
}