<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class akun extends Model
{
    protected $table = 'akun';
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'nama',
        'password',   
        'role',        
        'total_poin',
        'poin_digunakan',
        'id_sampah'
    ];

    // Relasi ke dataSampah
    public function dataSampah()
    {
        return $this->hasMany(dataSampah::class, 'id_user', 'id_user');
    }

    // Relasi ke transaksi
    public function transaksis()
    {
        return $this->hasMany(transaksi::class, 'id_user', 'id_user');
    }
}