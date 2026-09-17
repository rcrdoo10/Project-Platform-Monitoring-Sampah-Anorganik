<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_sampah', function (Blueprint $table) {
            $table->integer('id_sampah', 255)->primary();
            $table->integer('berat_botol');
            $table->integer('jumlah_botol');
            $table->integer('poin_dapat');
            $table->date('waktu_scan');
            $table->boolean('status_scan');
            $table->integer('id_user')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_sampah');
    }
};