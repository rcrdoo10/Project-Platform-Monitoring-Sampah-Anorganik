<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\reward;
use App\Models\transaksi;
use App\Models\transaksiReward;
use App\Models\akun;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class rewardController extends Controller
{
    // ========== VIEW UNTUK KOPMA ==========
    
    public function index()
    {
        if (!session('user_id')) {
            return redirect('/login');
        }
        if (session('user_role') != 'kopma') {
            return redirect('/' . session('user_role') . 'Dashboard');
        }

        $rewards = reward::all();
        $totalReward = reward::sum('stok');
        $totalTransaksi = transaksi::count();
        $transaksiPending = transaksi::where('status_transaksi', 0)->count();

        return view('kReward', [
            'title' => 'Reward',
            'rewards' => $rewards,
            'totalReward' => $totalReward,
            'totalTransaksi' => $totalTransaksi,
            'transaksiPending' => $transaksiPending
        ]);
    }
    
    // ========== API UNTUK WARGA ==========
    
    public function getTotalStok()
    {
        $totalStok = reward::sum('stok');
        
        return response()->json([
            'success' => true,
            'total_stok' => $totalStok
        ]);
    }
    
    public function getAllRewards()
    {
        $rewards = reward::all();
        
        return response()->json([
            'success' => true,
            'data' => $rewards
        ]);
    }
    
    // ========== API UNTUK MENDAPATKAN RIWAYAT PENUKARAN USER ==========
    
    public function getUserExchangeHistory()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
        
        try {
            $transaksis = DB::table('transaksi')
                ->leftJoin('transaksi_reward', 'transaksi.id_transaksi', '=', 'transaksi_reward.id_transaksi')
                ->leftJoin('reward', 'transaksi_reward.id_reward', '=', 'reward.id_reward')
                ->select(
                    'transaksi.id_transaksi',
                    'transaksi.tanggal_transaksi',
                    'transaksi.jam_transaksi',
                    'transaksi.poin_transaksi',
                    'transaksi.jumlah_transaksi',
                    'transaksi.status_transaksi',
                    DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
                )
                ->where('transaksi.id_user', $userId)
                ->groupBy('transaksi.id_transaksi', 'transaksi.tanggal_transaksi', 'transaksi.jam_transaksi', 'transaksi.poin_transaksi', 'transaksi.jumlah_transaksi', 'transaksi.status_transaksi')
                ->orderBy('transaksi.tanggal_transaksi', 'desc')
                ->get();
            
            $history = [];
            
            foreach ($transaksis as $transaksi) {
                $carbonDate = Carbon::parse($transaksi->tanggal_transaksi);
                $carbonDate->locale('id');
                $tanggalFormatted = $carbonDate->translatedFormat('j F Y');
                
                $jamFormatted = $transaksi->jam_transaksi 
                    ? substr($transaksi->jam_transaksi, 0, 5) 
                    : '00:00';
                
                $rewardNames = $transaksi->reward_names ?? 'Penukaran Poin';
                
                $history[] = [
                    'id' => $transaksi->id_transaksi,
                    'tanggal' => $tanggalFormatted . ', ' . $jamFormatted,
                    'reward' => $rewardNames,
                    'jumlah' => $transaksi->jumlah_transaksi ?? 1,
                    'total_poin' => $transaksi->poin_transaksi,
                    'status' => $transaksi->status_transaksi == 0 ? 'pending' : 'disetujui'
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $history
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getUserExchangeHistory: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ]);
        }
    }
    
    // ========== API UNTUK SEMUA RIWAYAT PENUKARAN (MODAL) ==========
    
    public function getAllHistoryReward()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
        
        try {
            $transaksis = DB::table('transaksi')
                ->leftJoin('transaksi_reward', 'transaksi.id_transaksi', '=', 'transaksi_reward.id_transaksi')
                ->leftJoin('reward', 'transaksi_reward.id_reward', '=', 'reward.id_reward')
                ->select(
                    'transaksi.id_transaksi',
                    'transaksi.tanggal_transaksi',
                    'transaksi.jam_transaksi',
                    'transaksi.poin_transaksi',
                    'transaksi.jumlah_transaksi',
                    'transaksi.status_transaksi',
                    DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
                )
                ->where('transaksi.id_user', $userId)
                ->groupBy('transaksi.id_transaksi', 'transaksi.tanggal_transaksi', 'transaksi.jam_transaksi', 'transaksi.poin_transaksi', 'transaksi.jumlah_transaksi', 'transaksi.status_transaksi')
                ->orderBy('transaksi.tanggal_transaksi', 'desc')
                ->get();
            
            $history = [];
            
            foreach ($transaksis as $transaksi) {
                $carbonDate = Carbon::parse($transaksi->tanggal_transaksi);
                $carbonDate->locale('id');
                $tanggalFormatted = $carbonDate->translatedFormat('j F Y');
                
                $jamFormatted = $transaksi->jam_transaksi 
                    ? substr($transaksi->jam_transaksi, 0, 5) 
                    : '00:00';
                
                $rewardNames = $transaksi->reward_names ?? 'Penukaran Poin';
                
                $history[] = [
                    'id' => $transaksi->id_transaksi,
                    'tanggal' => $tanggalFormatted . ', ' . $jamFormatted,
                    'reward' => $rewardNames,
                    'jumlah' => $transaksi->jumlah_transaksi ?? 1,
                    'total_poin' => $transaksi->poin_transaksi,
                    'status' => $transaksi->status_transaksi == 0 ? 'pending' : 'disetujui'
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $history
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getAllHistoryReward: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ]);
        }
    }
    
    // ========== API UNTUK PENUKARAN REWARD ==========
    
    public function exchangeReward(Request $request)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak login'
            ], 401);
        }
        
        $request->validate([
            'reward_id' => 'required',
            'quantity' => 'required|integer|min:1',
            'total_points' => 'required|integer'
        ]);
        
        DB::beginTransaction();
        
        try {
            $reward = reward::where('id_reward', $request->reward_id)->first();
            
            if (!$reward) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reward tidak ditemukan'
                ], 404);
            }
            
            if ($reward->stok < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi'
                ]);
            }
            
            $user = akun::where('id_user', $userId)->first();
            if ($user->total_poin < $request->total_points) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poin tidak mencukupi'
                ]);
            }
            
            $reward->stok -= $request->quantity;
            $reward->save();
            
            $user->total_poin -= $request->total_points;
            $user->save();
            
            session(['user_poin' => $user->total_poin]);
            
            $now = now();
            $transactionId = 'TXN' . time() . rand(100, 999);
            
            // 1. SIMPAN KE TABEL TRANSAKSI
            $transaksi = transaksi::create([
                'id_transaksi' => $transactionId,
                'id_user' => $userId,
                'poin_transaksi' => $request->total_points,
                'jumlah_transaksi' => $request->quantity,
                'tanggal_transaksi' => $now->toDateString(),
                'jam_transaksi' => $now->toTimeString(),
                'status_transaksi' => 0
            ]);
            
            // 2. SIMPAN KE TABEL TRANSAKSI_REWARD
            $transaksiReward = transaksiReward::create([
                'id_transaksi_reward' => 'TR' . time() . rand(100, 999),
                'id_transaksi' => $transactionId,
                'id_reward' => $reward->id_reward,
                'jumlah_reward' => $request->quantity
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Penukaran berhasil! Menunggu verifikasi admin.',
                'remaining_poin' => $user->total_poin,
                'reward_stock' => $reward->stok,
                'transaction_id' => $transactionId,
                'status' => 'pending',
                'quantity' => $request->quantity
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // ========== API UNTUK KOPMA  ==========
    
    public function store(Request $request)
    {
        try {
            if (!session('user_id') || session('user_role') != 'kopma') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $request->validate([
                'nama_reward' => 'required|string|max:255',
                'poin_dibutuhkan' => 'required|integer|min:0',
                'stok' => 'required|integer|min:0',
                'deskripsi' => 'nullable|string',
                'kategori' => 'required|string'
            ]);

            $jenisReward = 'barang';
            if ($request->kategori == 'Makanan') {
                $jenisReward = 'makanan';
            } elseif ($request->kategori == 'Minuman') {
                $jenisReward = 'minuman';
            }

            $reward = reward::create([
                'nama_reward' => $request->nama_reward,
                'deskripsi' => $request->deskripsi ?? '',
                'jenis_reward' => $jenisReward,
                'kategori' => $request->satuan ?? 'pcs',
                'poin_dibutuhkan' => (int)$request->poin_dibutuhkan,
                'stok' => (int)$request->stok,
                'gambar_reward' => 'default.jpg'
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Reward berhasil ditambahkan', 
                'data' => $reward
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error store reward: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!session('user_id') || session('user_role') != 'kopma') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $reward = reward::where('id_reward', $id)->first();
            
            if (!$reward) {
                return response()->json(['success' => false, 'message' => 'Reward tidak ditemukan'], 404);
            }

            $request->validate([
                'nama_reward' => 'required|string|max:255',
                'poin_dibutuhkan' => 'required|integer|min:0',
                'stok' => 'required|integer|min:0',
                'deskripsi' => 'nullable|string',
                'kategori' => 'required|string'
            ]);

            $jenisReward = 'barang';
            if ($request->kategori == 'Makanan') {
                $jenisReward = 'makanan';
            } elseif ($request->kategori == 'Minuman') {
                $jenisReward = 'minuman';
            }

            $reward->nama_reward = $request->nama_reward;
            $reward->deskripsi = $request->deskripsi;
            $reward->jenis_reward = $jenisReward;
            $reward->kategori = $request->satuan ?? 'pcs';
            $reward->poin_dibutuhkan = $request->poin_dibutuhkan;
            $reward->stok = $request->stok;
            $reward->save();

            return response()->json([
                'success' => true, 
                'message' => 'Reward berhasil diupdate',
                'data' => $reward
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error update reward: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!session('user_id') || session('user_role') != 'kopma') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $reward = reward::find($id);
        
        if (!$reward) {
            return response()->json(['success' => false, 'message' => 'Reward tidak ditemukan'], 404);
        }

        $reward->delete();
        
        return response()->json(['success' => true, 'message' => 'Reward berhasil dihapus']);
    }
}