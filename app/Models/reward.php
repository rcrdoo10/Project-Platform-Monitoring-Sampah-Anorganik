<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $table = 'reward';
    protected $primaryKey = 'id_reward';
    public $timestamps = false;
    
    protected $fillable = [
        'id_reward',
        'nama_reward',
        'jenis_reward',
        'stok',
        'poin_dibutuhkan',
        'gambar_reward',
        'deskripsi',
        'kategori'
    ];
}