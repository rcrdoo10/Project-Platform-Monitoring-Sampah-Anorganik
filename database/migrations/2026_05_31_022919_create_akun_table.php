<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun', function (Blueprint $table) {
            $table->integer('id_user')->primary()->autoIncrement();
            $table->string('nama', 255);
            $table->string('password', 255);
            $table->enum('role', ['warga', 'kopma', 'psl', 'rektorat', 'petugas']);
            $table->integer('total_poin')->default(0);
            $table->integer('poin_digunakan')->default(0);
            $table->string('id_sampah', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akun');
    }
};