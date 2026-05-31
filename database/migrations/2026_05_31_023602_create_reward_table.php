<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward', function (Blueprint $table) {
            $table->string('id_reward', 255)->primary();
            $table->string('nama_reward', 255);
            $table->enum('jenis_reward', ['makanan', 'minuman', 'barang']);
            $table->integer('stok');
            $table->integer('poin_dibutuhkan');
            $table->string('gambar_reward', 100);
            $table->string('deskripsi', 255)->nullable();
            $table->enum('kategori', ['pcs', 'lusin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward');
    }
};