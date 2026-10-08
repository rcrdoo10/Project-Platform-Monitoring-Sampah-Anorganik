<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\transaksi;
use App\Models\menukarkan;
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

            if ($transaksi->status_transaksi == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi sudah disetujui'
                ], 400);
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

        $formatted = $this->queryTransaksiByStatus(0);

        return response()->json(['success' => true, 'data' => $formatted]);
    }

    // ========== GET APPROVED TRANSAKSI ==========
    public function getApproved()
    {
        if (!session('user_id') || session('user_role') != 'kopma') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $formatted = $this->queryTransaksiByStatus(1);

        return response()->json(['success' => true, 'data' => $formatted]);
    }

    // ========== GET ALL TRANSAKSI ==========
    public function getAll()
    {
        if (!session('user_id') || session('user_role') != 'kopma') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $transaksis = DB::table('transaksi')
            ->leftJoin('menukarkan', 'transaksi.id_transaksi', '=', 'menukarkan.id_transaksi')
            ->leftJoin('reward', 'menukarkan.id_reward', '=', 'reward.id_reward')
            ->leftJoin('akun', 'transaksi.id_user', '=', 'akun.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',                      
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama as user_nama',
                DB::raw('SUM(menukarkan.jumlah_reward) as jumlah_transaksi'),
                DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
            )
            ->groupBy(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',                       // ✅ tambah
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama'
            )
            ->orderBy('transaksi.tanggal_transaksi', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $this->formatRows($transaksis),
        ]);
    }

    // ========== HELPER: QUERY BY STATUS ==========
    private function queryTransaksiByStatus($status)
    {
        $transaksis = DB::table('transaksi')
            ->leftJoin('menukarkan', 'transaksi.id_transaksi', '=', 'menukarkan.id_transaksi')
            ->leftJoin('reward', 'menukarkan.id_reward', '=', 'reward.id_reward')
            ->leftJoin('akun', 'transaksi.id_user', '=', 'akun.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',                
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama as user_nama',
                DB::raw('SUM(menukarkan.jumlah_reward) as jumlah_transaksi'),
                DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
            )
            ->where('transaksi.status_transaksi', $status)
            ->groupBy(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',                    
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama'
            )
            ->orderBy('transaksi.tanggal_transaksi', 'desc')
            ->get();

        return $this->formatRows($transaksis);
    }

    // ========== HELPER: FORMAT RESPONSE ==========
    private function formatRows($transaksis)
    {
        $formatted = [];

        foreach ($transaksis as $item) {
            $tanggalFormatted = \Carbon\Carbon::parse($item->tanggal_transaksi)
                ->locale('id')
                ->translatedFormat('j F Y');

            $formatted[] = [
                'id_transaksi' => $item->id_transaksi,
                'tanggal_transaksi' => $tanggalFormatted,     
                'jam_transaksi' => $item->jam_transaksi
                    ? substr($item->jam_transaksi, 0, 5)       
                    : '00:00',
                'poin_transaksi' => (int) $item->poin_transaksi,
                'jumlah_transaksi' => (int) ($item->jumlah_transaksi ?? 1),
                'status_transaksi' => (int) $item->status_transaksi,
                'user' => [
                    'id_user' => $item->id_user,
                    'nama' => $item->user_nama ?? 'User',
                ],
                'rewards' => $item->reward_names ? explode(', ', $item->reward_names) : [],
                'reward_names' => $item->reward_names ?? 'Penukaran Poin',
            ];
        }

        return $formatted;
    }
}