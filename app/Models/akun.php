<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class akun extends Model
{
    
    protected $table = 'akun';
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    
    protected $fillable = [
        'id_user',
        'nama',
        'password',
        'role',
        'total_poin',
        'poin_digunakan',
        'id_sampah'
    ];
    
    // Relasi ke data_sampah
    public function dataSampah()
    {
        return $this->belongsTo(dataSampah::class, 'id_user', 'id_user');
    }
}