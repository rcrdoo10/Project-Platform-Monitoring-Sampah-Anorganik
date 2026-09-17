<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\transaksi;
use App\Models\transaksiReward;
use App\Models\reward;
use Illuminate\Support\Facades\DB;

class transaksiController extends Controller
{
    // ========== APPROVE TRANSAKSI ==========
    public function approve($id)
    {
        try {
            if (!session('user_id') || session('user_role') != 'kopma') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized. Silakan login kembali.'
                ], 401);
            }
            
            $transaksi = transaksi::where('id_transaksi', $id)->first();
            
            if (!$transaksi) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }
            
            $transaksi->status_transaksi = 1;
            $transaksi->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disetujui'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // ========== GET PENDING TRANSAKSI ==========
    public function getPending()
    {
        if (!session('user_id') || session('user_role') != 'kopma') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $transaksis = DB::table('transaksi')
            ->leftJoin('transaksi_reward', 'transaksi.id_transaksi', '=', 'transaksi_reward.id_transaksi')
            ->leftJoin('reward', 'transaksi_reward.id_reward', '=', 'reward.id_reward')
            ->leftJoin('akun', 'transaksi.id_user', '=', 'akun.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',
                'transaksi.poin_transaksi',
                'transaksi.jumlah_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama',
                DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
            )
            ->where('transaksi.status_transaksi', 0)
            ->groupBy('transaksi.id_transaksi', 'transaksi.tanggal_transaksi', 'transaksi.jam_transaksi', 'transaksi.poin_transaksi', 'transaksi.jumlah_transaksi', 'transaksi.status_transaksi', 'akun.id_user', 'akun.nama')
            ->orderBy('transaksi.tanggal_transaksi', 'desc')
            ->get();
        
        $formatted = [];
        foreach ($transaksis as $item) {
            $formatted[] = [
                'id_transaksi' => $item->id_transaksi,
                'tanggal_transaksi' => $item->tanggal_transaksi,
                'jam_transaksi' => $item->jam_transaksi,
                'poin_transaksi' => $item->poin_transaksi,
                'jumlah_transaksi' => $item->jumlah_transaksi,
                'status_transaksi' => $item->status_transaksi,
                'user' => [
                    'id_user' => $item->id_user,
                    'nama' => $item->nama
                ],
                'rewards' => $item->reward_names ? explode(', ', $item->reward_names) : []
            ];
        }
        
        return response()->json(['success' => true, 'data' => $formatted]);
    }
    
    // ========== GET APPROVED TRANSAKSI ==========
    public function getApproved()
    {
        if (!session('user_id') || session('user_role') != 'kopma') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $transaksis = DB::table('transaksi')
            ->leftJoin('transaksi_reward', 'transaksi.id_transaksi', '=', 'transaksi_reward.id_transaksi')
            ->leftJoin('reward', 'transaksi_reward.id_reward', '=', 'reward.id_reward')
            ->leftJoin('akun', 'transaksi.id_user', '=', 'akun.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',
                'transaksi.poin_transaksi',
                'transaksi.jumlah_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama',
                DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
            )
            ->where('transaksi.status_transaksi', 1)
            ->groupBy('transaksi.id_transaksi', 'transaksi.tanggal_transaksi', 'transaksi.jam_transaksi', 'transaksi.poin_transaksi', 'transaksi.jumlah_transaksi', 'transaksi.status_transaksi', 'akun.id_user', 'akun.nama')
            ->orderBy('transaksi.tanggal_transaksi', 'desc')
            ->get();
        
        $formatted = [];
        foreach ($transaksis as $item) {
            $formatted[] = [
                'id_transaksi' => $item->id_transaksi,
                'tanggal_transaksi' => $item->tanggal_transaksi,
                'jam_transaksi' => $item->jam_transaksi,
                'poin_transaksi' => $item->poin_transaksi,
                'jumlah_transaksi' => $item->jumlah_transaksi,
                'status_transaksi' => $item->status_transaksi,
                'user' => [
                    'id_user' => $item->id_user,
                    'nama' => $item->nama
                ],
                'rewards' => $item->reward_names ? explode(', ', $item->reward_names) : []
            ];
        }
        
        return response()->json(['success' => true, 'data' => $formatted]);
    }
    
    // ========== GET ALL TRANSAKSI ==========
    public function getAll()
    {
        if (!session('user_id') || session('user_role') != 'kopma') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $transaksis = transaksi::with('user')
            ->orderBy('tanggal_transaksi', 'desc')
            ->get();
        
        return response()->json(['success' => true, 'data' => $transaksis]);
    }
}