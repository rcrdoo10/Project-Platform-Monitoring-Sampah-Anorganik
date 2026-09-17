<?php

namespace App\Http\Controllers;

use App\Models\akun;
use App\Models\dataSampah;
use App\Models\reward;
use App\Models\transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
        
        $totalBotol = dataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->sum('jumlah_botol');
        
        $totalBerat = dataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->sum('berat_botol');
        
        session([
            'user_poin' => $user->total_poin ?? 0,
            'user_nama' => $user->nama,
        ]);
        
        return view('wDashboard', [
            'title' => 'Dashboard',
            'user_nama' => $user->nama ?? 'User',
            'user_poin' => $user->total_poin ?? 0,
            'totalBotol' => $totalBotol ?? 0,
            'totalBerat' => $totalBerat ?? 0
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
            'user_nama' => session('user_nama', 'User')
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
                'user_nama' => session('user_nama', 'User')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error wReward: ' . $e->getMessage());
            
            return view('wReward', [
                'title' => 'Reward',
                'rewards' => [],
                'user_poin' => session('user_poin', 0),
                'user_nama' => session('user_nama', 'User')
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

        // 🔥 AMBIL TRANSAKSI DENGAN JOIN KE transaksi_reward DAN reward
        $recentTransactions = DB::table('transaksi')
            ->leftJoin('transaksi_reward', 'transaksi.id_transaksi', '=', 'transaksi_reward.id_transaksi')
            ->leftJoin('reward', 'transaksi_reward.id_reward', '=', 'reward.id_reward')
            ->leftJoin('akun', 'transaksi.id_user', '=', 'akun.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.nama as user_nama',
                DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
            )
            ->groupBy('transaksi.id_transaksi', 'transaksi.tanggal_transaksi', 'transaksi.poin_transaksi', 'transaksi.status_transaksi', 'akun.nama')
            ->orderBy('transaksi.tanggal_transaksi', 'DESC')
            ->limit(10)
            ->get();

        // Format ulang data
        $formattedRecent = [];
        foreach ($recentTransactions as $item) {
            $formattedRecent[] = (object)[
                'id_transaksi' => $item->id_transaksi,
                'tanggal_transaksi' => $item->tanggal_transaksi,
                'poin_transaksi' => $item->poin_transaksi,
                'status_transaksi' => $item->status_transaksi,
                'user' => (object)['nama' => $item->user_nama],
                'rewards' => $item->reward_names ? array_map(function($name) {
                    return (object)['nama_reward' => $name];
                }, explode(', ', $item->reward_names)) : []
            ];
        }

        // Ambil semua transaksi untuk modal
        $allTransactions = DB::table('transaksi')
            ->leftJoin('transaksi_reward', 'transaksi.id_transaksi', '=', 'transaksi_reward.id_transaksi')
            ->leftJoin('reward', 'transaksi_reward.id_reward', '=', 'reward.id_reward')
            ->leftJoin('akun', 'transaksi.id_user', '=', 'akun.id_user')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.nama as user_nama',
                DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names')
            )
            ->groupBy('transaksi.id_transaksi', 'transaksi.tanggal_transaksi', 'transaksi.poin_transaksi', 'transaksi.status_transaksi', 'akun.nama')
            ->orderBy('transaksi.tanggal_transaksi', 'DESC')
            ->get();

        $formattedAll = [];
        foreach ($allTransactions as $item) {
            $formattedAll[] = (object)[
                'id_transaksi' => $item->id_transaksi,
                'tanggal_transaksi' => $item->tanggal_transaksi,
                'poin_transaksi' => $item->poin_transaksi,
                'status_transaksi' => $item->status_transaksi,
                'user' => (object)['nama' => $item->user_nama],
                'rewards' => $item->reward_names ? array_map(function($name) {
                    return (object)['nama_reward' => $name];
                }, explode(', ', $item->reward_names)) : []
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
            'allTransactions' => $formattedAll
        ]);
    }

    public function verifikasi()
    {
        if (!session('user_id')) {
            return redirect('/login');
        }
        if (session('user_role') != 'kopma') {
            return redirect('/' . session('user_role') . 'Dashboard');
        }

        $pendingTransactions = transaksi::with('user')
            ->where('status_transaksi', 0)
            ->orderBy('tanggal_transaksi', 'DESC')
            ->get();

        $approvedTransactions = transaksi::with('user')
            ->where('status_transaksi', 1)
            ->orderBy('tanggal_transaksi', 'DESC')
            ->get();

        return view('kVerifikasi', [
            'title' => 'Verifikasi Transaksi',
            'pendingTransactions' => $pendingTransactions,
            'approvedTransactions' => $approvedTransactions,
            'user_nama' => session('user_nama', 'Admin')
        ]);
    }

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
        
        $totalBotol = dataSampah::where('status_scan', 'success')->sum('jumlah_botol');
        $totalBerat = dataSampah::where('status_scan', 'success')->sum('berat_botol');
        
        return view('laporan', [
            'title' => 'Laporan',
            'totalBotol' => $totalBotol ?? 0,
            'totalBerat' => $totalBerat ?? 0,
            'user_nama' => session('user_nama', 'Petugas'),
            'user_role' => session('user_role')
        ]);
    }
}