<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataSampah;
use App\Models\akun;
use Illuminate\Support\Facades\DB;

class sampahController extends Controller
{
    // Method untuk menampilkan data sampah yang perlu diverifikasi
    public function index()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect('/login');
        }
        
        // Ambil data sampah dengan status 'pending' atau 'menunggu'
        $dataSampah = DataSampah::where('status_scan', 'pending')
            ->orWhere('status_scan', 'menunggu')
            ->orderBy('waktu_scan', 'desc')
            ->get();
        
        return view('kVerifikasi', [
            'title' => 'Verifikasi Sampah',
            'dataSampah' => $dataSampah
        ]);
    }
    
    // Method untuk verifikasi dan memberikan poin
    public function verifikasi(Request $request)
    {
        $request->validate([
            'id_sampah' => 'required|integer',
            'status' => 'required|in:success,failed'
        ]);
        
        DB::beginTransaction();
        
        try {
            $sampah = DataSampah::where('id_sampah', $request->id_sampah)->first();
            
            if (!$sampah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sampah tidak ditemukan'
                ]);
            }
            
            // Jika status verifikasi SUCCESS, berikan poin
            if ($request->status == 'success') {
                // Update status sampah
                $sampah->status_scan = 'success';
                $sampah->save();
                
                // Update poin user
                $user = akun::where('id_user', $sampah->id_user)->first();
                if ($user) {
                    $user->total_poin = ($user->total_poin ?? 0) + $sampah->poin_dapat;
                    $user->save();
                    
                    // Update session
                    if (session('user_id') == $sampah->id_user) {
                        session(['user_poin' => $user->total_poin]);
                    }
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Verifikasi berhasil! Poin telah ditambahkan.',
                    'poin' => $sampah->poin_dapat
                ]);
            } 
            // Jika status verifikasi FAILED, tidak berikan poin
            else {
                $sampah->status_scan = 'failed';
                $sampah->save();
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Verifikasi gagal. Poin tidak ditambahkan.',
                    'poin' => 0
                ]);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
    
    // Method untuk verifikasi massal
    public function verifikasiMassal(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'status' => 'required|in:success,failed'
        ]);
        
        DB::beginTransaction();
        
        try {
            $totalPoin = 0;
            $successCount = 0;
            
            foreach ($request->ids as $id) {
                $sampah = DataSampah::where('id_sampah', $id)->first();
                
                if ($sampah && $sampah->status_scan != 'success') {
                    $sampah->status_scan = $request->status;
                    $sampah->save();
                    
                    if ($request->status == 'success') {
                        $user = akun::where('id_user', $sampah->id_user)->first();
                        if ($user) {
                            $user->total_poin = ($user->total_poin ?? 0) + $sampah->poin_dapat;
                            $user->save();
                            $totalPoin += $sampah->poin_dapat;
                        }
                    }
                    $successCount++;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil memverifikasi $successCount data!",
                'total_poin' => $totalPoin
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
    
    // Method untuk mendapatkan detail sampah
    public function detail($id)
    {
        $sampah = DataSampah::with('user')->where('id_sampah', $id)->first();
        
        if (!$sampah) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => $sampah
        ]);
    }
}