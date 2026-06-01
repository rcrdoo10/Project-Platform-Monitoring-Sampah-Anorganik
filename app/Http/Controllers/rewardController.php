<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reward;

class rewardController extends Controller
{
    // Method untuk mendapatkan total stok reward
    public function getTotalStok()
    {
        $totalStok = Reward::sum('stok');
        
        return response()->json([
            'success' => true,
            'total_stok' => $totalStok
        ]);
    }
    
    // Method untuk mendapatkan semua data reward
    public function getAllRewards()
    {
        $rewards = Reward::all();
        
        return response()->json([
            'success' => true,
            'data' => $rewards
        ]);
    }
    
    // Method untuk menyimpan reward baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_reward' => 'required|string|max:255',
            'jenis_reward' => 'required|string',
            'stok' => 'required|integer|min:0',
            'poin_dibutuhkan' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|string'
        ]);
        
        $reward = Reward::create([
            'nama_reward' => $request->nama_reward,
            'jenis_reward' => $request->jenis_reward,
            'stok' => $request->stok,
            'poin_dibutuhkan' => $request->poin_dibutuhkan,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Reward berhasil ditambahkan',
            'data' => $reward
        ]);
    }
    
    // Method untuk update reward
    public function update(Request $request, $id)
    {
        $reward = Reward::find($id);
        
        if (!$reward) {
            return response()->json([
                'success' => false,
                'message' => 'Reward tidak ditemukan'
            ], 404);
        }
        
        $request->validate([
            'nama_reward' => 'required|string|max:255',
            'jenis_reward' => 'required|string',
            'stok' => 'required|integer|min:0',
            'poin_dibutuhkan' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|string'
        ]);
        
        $reward->update([
            'nama_reward' => $request->nama_reward,
            'jenis_reward' => $request->jenis_reward,
            'stok' => $request->stok,
            'poin_dibutuhkan' => $request->poin_dibutuhkan,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Reward berhasil diupdate',
            'data' => $reward
        ]);
    }
    
    // Method untuk hapus reward
    public function destroy($id)
    {
        $reward = Reward::find($id);
        
        if (!$reward) {
            return response()->json([
                'success' => false,
                'message' => 'Reward tidak ditemukan'
            ], 404);
        }
        
        $reward->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Reward berhasil dihapus'
        ]);
    }
}