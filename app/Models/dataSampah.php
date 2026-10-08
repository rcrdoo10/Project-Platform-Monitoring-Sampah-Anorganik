<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class dataSampah extends Model
{
    protected $table = 'data_sampah';
    protected $primaryKey = 'id_sampah';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_sampah',
        'berat_botol',
        'total_botol',
        'poin_dapat',
        'waktu_scan',
        'status_scan',
    ];

    public function mencatats()
    {
        return $this->hasMany(mencatat::class, 'id_sampah', 'id_sampah');
    }
}