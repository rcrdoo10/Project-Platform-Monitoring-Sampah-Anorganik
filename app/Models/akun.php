<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class akun extends Model
{
    protected $table = 'akun';
    protected $primaryKey = 'id_user';
    public $timestamps = false;  
    
    protected $fillable = [
        'nama',
        'password',
        'role',
        'total_poin',
        'poin_digunakan',
        'id_sampah'
    ];
}