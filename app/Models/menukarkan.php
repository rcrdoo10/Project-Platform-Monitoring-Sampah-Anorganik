<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class menukarkan extends Model
{
    protected $table = 'menukarkan';
    protected $primaryKey = 'id_menukarkan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_menukarkan',
        'jumlah_reward',
        'id_transaksi',
        'id_reward',
    ];

    public function transaksi()
    {
        return $this->belongsTo(transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function reward()
    {
        return $this->belongsTo(reward::class, 'id_reward', 'id_reward');
    }
}