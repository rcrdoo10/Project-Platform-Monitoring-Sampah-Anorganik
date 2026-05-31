<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSampah extends Model
{
    protected $table = 'data_sampah';
    protected $primaryKey = 'id_sampah';
    public $timestamps = false;
    
    protected $fillable = [
        'id_sampah',
        'berat_botol',
        'jumlah_botol',
        'poin_dapat',
        'waktu_scan',
        'status_scan',
        'id_user'
    ];
}