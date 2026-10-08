<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mencatat extends Model
{
    protected $table = 'mencatat';
    protected $primaryKey = 'id_mencatat';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_mencatat',
        'tanggal',
        'waktu',
        'jumlah_botol',
        'id_user',
        'id_sampah',
    ];

    public function akun()
    {
        return $this->belongsTo(akun::class, 'id_user', 'id_user');
    }

    public function dataSampah()
    {
        return $this->belongsTo(dataSampah::class, 'id_sampah', 'id_sampah');
    }
}