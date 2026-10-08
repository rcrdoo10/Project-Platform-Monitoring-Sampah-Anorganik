<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class akun extends Model
{
    protected $table = 'akun';
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'nama',
        'password',
        'role',
        'email',
        'poin_tersisa',
        'total_poin',
    ];

    public function transaksis()
    {
        return $this->hasMany(transaksi::class, 'id_user', 'id_user');
    }

    public function mencatats()
    {
        return $this->hasMany(mencatat::class, 'id_user', 'id_user');
    }
}