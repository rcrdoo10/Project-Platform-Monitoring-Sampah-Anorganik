<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class akunSampah extends Model
{
    protected $table = 'akun_sampah';
    protected $primaryKey = 'id_akun_sampah';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'id_akun_sampah',
        'tanggal',
        'waktu',
        'id_user',
        'id_sampah'
    ];
    
    // Relasi ke akun
    public function akun()
    {
        return $this->belongsTo(akun::class, 'id_user', 'id_user');
    }
    
    // Relasi ke dataSampah
    public function dataSampah()
    {
        return $this->belongsTo(dataSampah::class, 'id_sampah', 'id_sampah');
    }
}