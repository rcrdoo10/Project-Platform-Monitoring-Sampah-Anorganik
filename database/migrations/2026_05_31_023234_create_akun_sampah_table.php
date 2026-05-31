<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun_sampah', function (Blueprint $table) {
            $table->string('id_akun_sampah', 255)->primary();
            $table->date('tanggal');
            $table->time('waktu');
            $table->integer('id_user')->nullable();
            $table->string('id_sampah', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akun_sampah');
    }
};