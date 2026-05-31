<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_reward', function (Blueprint $table) {
            $table->string('id_transaksi_reward', 255)->primary();
            $table->integer('jumlah_reward');
            $table->string('id_transaksi', 255)->nullable();
            $table->string('id_reward', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_reward');
    }
};