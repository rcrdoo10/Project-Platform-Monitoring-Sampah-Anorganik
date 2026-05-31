<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkunSampah extends Model
{
    protected $table = 'akun_sampah';
    protected $primaryKey = 'id_akun_sampah';
    public $timestamps = false;
    
    protected $fillable = [
        'id_akun_sampah',
        'tanggal',
        'waktu',
        'id_user',
        'id_sampah'
    ];
}