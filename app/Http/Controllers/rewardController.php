<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reward;
use App\Models\Transaksi; // Sesuaikan dengan model transaksi Anda

class rewardController extends Controller
{
    // Method untuk dashboard Kopma
    public function getDashboardData()
    {
        // Hitung total reward dari database (jumlah reward yang stoknya > 0)
        $totalReward = Reward::where('stok', '>', 0)->count();
        
        // Alternatif: jika ingin total stok semua reward
        // $totalReward = Reward::sum('stok');
        
        // Hitung transaksi pending (sesuaikan dengan tabel transaksi Anda)
        $pendingCount = Transaksi::where('status', 'pending')->count() ?? 0;
        
        // Hitung total transaksi
        $totalTransaksi = Transaksi::count() ?? 0;
        
        // Ambil data reward untuk ditampilkan
        $rewards = Reward::all();
        
        return response()->json([
            'success' => true,
            'totalReward' => $totalReward,
            'pendingCount' => $pendingCount,
            'totalTransaksi' => $totalTransaksi,
            'rewards' => $rewards
        ]);
    }
    
    // Method untuk mengambil semua reward
    public function index()
    {
        $rewards = Reward::all();
        return response()->json([
            'success' => true,
            'data' => $rewards
        ]);
    }
    
    // Method untuk mengambil reward yang tersedia (stok > 0)
    public function getAvailableRewards()
    {
        $rewards = Reward::where('stok', '>', 0)->get();
        return response()->json([
            'success' => true,
            'data' => $rewards,
            'total' => $rewards->count()
        ]);
    }
}