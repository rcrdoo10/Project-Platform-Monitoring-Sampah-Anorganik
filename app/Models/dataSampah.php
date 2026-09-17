<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class dataSampah extends Model
{
    protected $table = 'data_sampah';
    protected $primaryKey = 'id_sampah';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'id_sampah',
        'berat_botol',
        'jumlah_botol',
        'poin_dapat',
        'waktu_scan',
        'jam_scan',
        'status_scan',
        'id_user'
    ];
    
    public function user()
    {
        return $this->belongsTo(akun::class, 'id_user', 'id_user');
    }
    
    public function akunSampah()
    {
        return $this->hasMany(akunSampah::class, 'id_sampah', 'id_sampah');
    }
}