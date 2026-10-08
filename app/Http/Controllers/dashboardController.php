<?php

namespace App\Http\Controllers;

use App\Models\akun;
use App\Models\dataSampah;
use App\Models\mencatat;
use App\Models\reward;
use App\Models\transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class dashboardController extends Controller
{
    // ========== WARGA ==========

    public function warga()
    {
        if (!session('user_id')) {
            return redirect('/login');
        }
        if (session('user_role') != 'warga') {
            return redirect('/' . session('user_role') . 'Dashboard');
        }

        $userId = session('user_id');
        $user = akun::where('id_user', $userId)->first();

        if (!$user) {
            session()->flush();
            return redirect('/login');
        }

        $totalBotol = mencatat::join('data_sampah', 'mencatat.id_sampah', '=', 'data_sampah.id_sampah')
            ->where('mencatat.id_user', $userId)
            ->where('data_sampah.status_scan', 'success')
            ->sum('mencatat.jumlah_botol');

        $totalBerat = mencatat::join('data_sampah', 'mencatat.id_sampah', '=', 'data_sampah.id_sampah')
            ->where('mencatat.id_user', $userId)
            ->where('data_sampah.status_scan', 'success')
            ->sum('data_sampah.berat_botol');

        session([
            'user_poin' => $user->total_poin ?? 0,
            'user_nama' => $user->nama,
        ]);

        return view('wDashboard', [
            'title' => 'Dashboard',
            'user_nama' => $user->nama ?? 'User',
            'user_poin' => $user->total_poin ?? 0,
            'totalBotol' => $totalBotol ?? 0,
            'totalBerat' => $totalBerat ?? 0,
        ]);
    }

    public function wTukarBotol()
    {
        if (!session('user_id')) {
            return redirect('/login');
        }
        if (session('user_role') != 'warga') {
            return redirect('/' . session('user_role') . 'Dashboard');
        }

        return view('wTukarBotol', [
            'title' => 'Tukar Botol',
            'user_poin' => session('user_poin', 0),
            'user_nama' => session('user_nama', 'User'),
        ]);
    }

    public function wReward()
    {
        if (!session('user_id')) {
            return redirect('/login');
        }
        if (session('user_role') != 'warga') {
            return redirect('/' . session('user_role') . 'Dashboard');
        }

        try {
            $rewards = reward::all();

            return view('wReward', [
                'title' => 'Reward',
                'rewards' => $rewards,
                'user_poin' => session('user_poin', 0),
                'user_nama' => session('user_nama', 'User'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error wReward: ' . $e->getMessage());

            return view('wReward', [
                'title' => 'Reward',
                'rewards' => [],
                'user_poin' => session('user_poin', 0),
                'user_nama' => session('user_nama', 'User'),
            ]);
        }
    }

    // ========== KOPMA ==========

    public function kopma(Request $request)
    {
        if (!session('user_id')) {
            return redirect('/login');
        }
        if (session('user_role') != 'kopma') {
            return redirect('/' . session('user_role') . 'Dashboard');
        }

        $period = $request->get('period', 'daily');

        $totalReward = reward::sum('stok');
        $totalTransaksi = transaksi::count();
        $transaksiPending = transaksi::where('status_transaksi', 0)->count();

        $chartData = $this->getChartDataByPeriod($period);
        $labels = $chartData['labels'];
        $data = $chartData['data'];

        $recentTransactions = DB::table('transaksi')
            ->leftJoin('menukarkan', 'transaksi.id_transaksi', '=', 'menukarkan.id_transaksi')
            ->leftJoin('reward', 'menukarkan.id_reward', '=', 'reward.id_reward')
            ->leftJoin('akun', 'transaksi.id_user', '=', 'akun.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',                  
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.nama as user_nama',
                DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
            )
            ->groupBy(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',                 
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.nama'
            )
            ->orderBy('transaksi.tanggal_transaksi', 'DESC')
            ->limit(10)
            ->get();

        $formattedRecent = [];
        foreach ($recentTransactions as $item) {
            $formattedRecent[] = (object)[
                'id_transaksi' => $item->id_transaksi,
                'tanggal_transaksi' => $item->tanggal_transaksi,
                'jam_transaksi' => $item->jam_transaksi,    
                'poin_transaksi' => $item->poin_transaksi,
                'status_transaksi' => $item->status_transaksi,
                'user' => (object)['nama' => $item->user_nama],
                'rewards' => $item->reward_names ? array_map(function ($name) {
                    return (object)['nama_reward' => $name];
                }, explode(', ', $item->reward_names)) : [],
            ];
        }

        $allTransactions = DB::table('transaksi')
            ->leftJoin('menukarkan', 'transaksi.id_transaksi', '=', 'menukarkan.id_transaksi')
            ->leftJoin('reward', 'menukarkan.id_reward', '=', 'reward.id_reward')
            ->leftJoin('akun', 'transaksi.id_user', '=', 'akun.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',                  
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.nama as user_nama',
                DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
            )
            ->groupBy(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',                  
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.nama'
            )
            ->orderBy('transaksi.tanggal_transaksi', 'DESC')
            ->get();

        $formattedAll = [];
        foreach ($allTransactions as $item) {
            $formattedAll[] = (object)[
                'id_transaksi' => $item->id_transaksi,
                'tanggal_transaksi' => $item->tanggal_transaksi,
                'jam_transaksi' => $item->jam_transaksi,     
                'poin_transaksi' => $item->poin_transaksi,
                'status_transaksi' => $item->status_transaksi,
                'user' => (object)['nama' => $item->user_nama],
                'rewards' => $item->reward_names ? array_map(function ($name) {
                    return (object)['nama_reward' => $name];
                }, explode(', ', $item->reward_names)) : [],
            ];
        }

        return view('kDashboard', [
            'title' => 'Dashboard',
            'totalReward' => $totalReward,
            'totalTransaksi' => $totalTransaksi,
            'transaksiPending' => $transaksiPending,
            'chartLabels' => $labels,
            'chartData' => $data,
            'currentPeriod' => $period,
            'recentTransactions' => $formattedRecent,
            'allTransactions' => $formattedAll,
        ]);
    }

    // ========== HALAMAN VERIFIKASI (KOPMA) ==========

    public function verifikasi()
    {
        if (!session('user_id')) {
            return redirect('/login');
        }
        if (session('user_role') != 'kopma') {
            return redirect('/' . session('user_role') . 'Dashboard');
        }

        return view('kVerifikasi', [
            'title' => 'Verifikasi Transaksi',
            'user_nama' => session('user_nama', 'Admin'),
        ]);
    }

    // ========== API VERIFIKASI TRANSAKSI ==========

    public function apiPendingTransactions()
    {
        if (!session('user_id') || session('user_role') != 'kopma') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $rows = DB::table('transaksi')
            ->leftJoin('menukarkan', 'transaksi.id_transaksi', '=', 'menukarkan.id_transaksi')
            ->leftJoin('reward', 'menukarkan.id_reward', '=', 'reward.id_reward')
            ->leftJoin('akun', 'transaksi.id_user', '=', 'akun.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama as user_nama',
                DB::raw('SUM(menukarkan.jumlah_reward) as jumlah_transaksi'),
                DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
            )
            ->where('transaksi.status_transaksi', 0)
            ->groupBy(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama'
            )
            ->orderBy('transaksi.tanggal_transaksi', 'DESC')
            ->get();

        $data = $rows->map(function ($row) {
            return [
                'id_transaksi' => $row->id_transaksi,
                'tanggal_transaksi' => $row->tanggal_transaksi,
                'jam_transaksi' => '00:00',
                'jumlah_transaksi' => (int) ($row->jumlah_transaksi ?? 1),
                'poin_transaksi' => (int) $row->poin_transaksi,
                'status_transaksi' => (int) $row->status_transaksi,
                'reward_names' => $row->reward_names ?? 'Penukaran Poin',
                'user' => [
                    'id_user' => $row->id_user,
                    'nama' => $row->user_nama ?? 'User',
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function apiApprovedTransactions()
    {
        if (!session('user_id') || session('user_role') != 'kopma') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $rows = DB::table('transaksi')
            ->leftJoin('menukarkan', 'transaksi.id_transaksi', '=', 'menukarkan.id_transaksi')
            ->leftJoin('reward', 'menukarkan.id_reward', '=', 'reward.id_reward')
            ->leftJoin('akun', 'transaksi.id_user', '=', 'akun.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama as user_nama',
                DB::raw('SUM(menukarkan.jumlah_reward) as jumlah_transaksi'),
                DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
            )
            ->where('transaksi.status_transaksi', 1)
            ->groupBy(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama'
            )
            ->orderBy('transaksi.tanggal_transaksi', 'DESC')
            ->get();

        $data = $rows->map(function ($row) {
            return [
                'id_transaksi' => $row->id_transaksi,
                'tanggal_transaksi' => $row->tanggal_transaksi,
                'jam_transaksi' => '00:00',
                'jumlah_transaksi' => (int) ($row->jumlah_transaksi ?? 1),
                'poin_transaksi' => (int) $row->poin_transaksi,
                'status_transaksi' => (int) $row->status_transaksi,
                'reward_names' => $row->reward_names ?? 'Penukaran Poin',
                'user' => [
                    'id_user' => $row->id_user,
                    'nama' => $row->user_nama ?? 'User',
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function apiApproveTransaction($id)
    {
        if (!session('user_id') || session('user_role') != 'kopma') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $trx = transaksi::where('id_transaksi', $id)->first();

        if (!$trx) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }

        if ($trx->status_transaksi == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah disetujui',
            ], 400);
        }

        $trx->status_transaksi = 1;
        $trx->save();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil disetujui',
        ]);
    }

    // ========== CHART DATA ==========

    private function getChartDataByPeriod($period)
    {
        switch ($period) {
            case 'weekly':
                $labels = [];
                $data = [];
                for ($i = 5; $i >= 0; $i--) {
                    $startDate = now()->subWeeks($i)->startOfWeek();
                    $endDate = now()->subWeeks($i)->endOfWeek();
                    $weekNumber = now()->subWeeks($i)->format('W');

                    $total = transaksi::where('status_transaksi', 1)
                        ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                        ->count();

                    $labels[] = 'Minggu ' . $weekNumber;
                    $data[] = $total;
                }
                break;

            case 'monthly':
                $labels = [];
                $data = [];
                for ($i = 11; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $monthName = $month->format('F Y');

                    $total = transaksi::where('status_transaksi', 1)
                        ->whereYear('tanggal_transaksi', $month->year)
                        ->whereMonth('tanggal_transaksi', $month->month)
                        ->count();

                    $labels[] = $monthName;
                    $data[] = $total;
                }
                break;

            case 'daily':
            default:
                $labels = [];
                $data = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $dateLabel = $date->format('d M');

                    $total = transaksi::where('status_transaksi', 1)
                        ->whereDate('tanggal_transaksi', $date->toDateString())
                        ->count();

                    $labels[] = $dateLabel;
                    $data[] = $total;
                }
                break;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    // ========== HALAMAN LAPORAN (PETUGAS / PSL / REKTORAT) ==========

    public function laporan()
    {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $allowedRoles = ['petugas', 'psl', 'rektorat'];
        $userRole = session('user_role');

        if (!in_array($userRole, $allowedRoles)) {
            if ($userRole == 'warga') return redirect('/wDashboard');
            if ($userRole == 'kopma') return redirect('/kDashboard');
            return redirect('/login');
        }

        $totalBotol = mencatat::join('data_sampah', 'mencatat.id_sampah', '=', 'data_sampah.id_sampah')
            ->where('data_sampah.status_scan', 'success')
            ->sum('mencatat.jumlah_botol');

        $totalBerat = mencatat::join('data_sampah', 'mencatat.id_sampah', '=', 'data_sampah.id_sampah')
            ->where('data_sampah.status_scan', 'success')
            ->sum('data_sampah.berat_botol');

        return view('laporan', [
            'title' => 'Laporan',
            'totalBotol' => $totalBotol ?? 0,
            'totalBerat' => $totalBerat ?? 0,
            'user_nama' => session('user_nama', 'Petugas'),
            'user_role' => session('user_role'),
        ]);
    }
}