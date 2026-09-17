<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\akun;
use App\Models\dataSampah;
use Illuminate\Support\Facades\DB;

class poinController extends Controller
{
    public function getAllStats()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'poin' => 0,
                'total_botol' => 0,
                'total_berat' => 0
            ]);
        }
        
        $user = akun::where('id_user', $userId)->first();
        
        $totalBotol = dataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->sum('jumlah_botol');
        
        $totalBerat = dataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->sum('berat_botol');
        
        return response()->json([
            'success' => true,
            'poin' => $user->total_poin ?? 0,
            'total_botol' => $totalBotol ?? 0,
            'total_berat' => $totalBerat ?? 0,
            'nama' => $user->nama ?? ''
        ]);
    }
    
    public function getPoin()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'poin' => 0
            ]);
        }
        
        $user = akun::where('id_user', $userId)->first();
        
        return response()->json([
            'success' => true,
            'poin' => $user->total_poin ?? 0,
            'nama' => $user->nama ?? ''
        ]);
    }
    
    public function getTotalBotol()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'total_botol' => 0
            ]);
        }
        
        $totalBotol = dataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->sum('jumlah_botol');
        
        return response()->json([
            'success' => true,
            'total_botol' => $totalBotol ?? 0
        ]);
    }
    
    public function getTotalBerat()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'total_berat' => 0
            ]);
        }
        
        $totalBerat = dataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->sum('berat_botol');
        
        return response()->json([
            'success' => true,
            'total_berat' => $totalBerat ?? 0
        ]);
    }
    
    public function getChartStats()
{
    $userId = session('user_id');
    
    if (!$userId) {
        return response()->json([
            'success' => false,
            'labels' => [],
            'poin_data' => [],
            'botol_data' => [],
            'berat_data' => []
        ]);
    }
    
    $minDate = dataSampah::where('id_user', $userId)
        ->where('status_scan', 'success')
        ->min('waktu_scan');
    
    $maxDate = dataSampah::where('id_user', $userId)
        ->where('status_scan', 'success')
        ->max('waktu_scan');
    
    if (!$minDate || !$maxDate) {
        return response()->json([
            'success' => true,
            'labels' => ['Belum ada data'],
            'poin_data' => [0],
            'botol_data' => [0],
            'berat_data' => [0],
            'period_label' => 'Belum ada data scan'
        ]);
    }
    
    $startDate = \Carbon\Carbon::parse($minDate);
    $endDate = \Carbon\Carbon::parse($maxDate);
    $daysDiff = $startDate->diffInDays($endDate);
    
    if ($daysDiff <= 30) {
        return $this->getDailyData($userId, $startDate, $endDate);
    } elseif ($daysDiff <= 180) {
        return $this->getWeeklyChartData($userId, $startDate, $endDate);
    } else {
        return $this->getMonthlyChartData($userId, $startDate, $endDate);
    }
}
    
    private function getDailyData($userId, $startDate, $endDate)
    {
        $labels = [];
        $poinData = [];
        $botolData = [];
        $beratData = [];
        
        $dailyData = dataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->whereBetween('waktu_scan', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(waktu_scan) as tanggal'),
                DB::raw('SUM(poin_dapat) as total_poin'),
                DB::raw('SUM(jumlah_botol) as total_botol'),
                DB::raw('SUM(berat_botol) as total_berat')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();
        
        $dataByDate = [];
        foreach ($dailyData as $item) {
            $dataByDate[$item->tanggal] = [
                'poin' => $item->total_poin,
                'botol' => $item->total_botol,
                'berat' => $item->total_berat
            ];
        }
        
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('d M');
            $poinData[] = isset($dataByDate[$dateStr]) ? $dataByDate[$dateStr]['poin'] : 0;
            $botolData[] = isset($dataByDate[$dateStr]) ? $dataByDate[$dateStr]['botol'] : 0;
            $beratData[] = isset($dataByDate[$dateStr]) ? $dataByDate[$dateStr]['berat'] : 0;
            $currentDate->addDay();
        }
        
        $periodText = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');
        
        return response()->json([
            'success' => true,
            'labels' => $labels,
            'poin_data' => $poinData,
            'botol_data' => $botolData,
            'berat_data' => $beratData,
            'period_label' => 'Periode: ' . $periodText . ' (' . count($labels) . ' hari)'
        ]);
    }
    
    private function getWeeklyChartData($userId, $startDate, $endDate)
    {
        $labels = [];
        $poinData = [];
        $botolData = [];
        $beratData = [];
        $weeks = [];
        
        $startOfWeek = $startDate->copy()->startOfWeek();
        $endOfWeek = $endDate->copy()->endOfWeek();
        
        $currentStart = $startOfWeek->copy();
        $weekNumber = 1;
        
        while ($currentStart <= $endOfWeek) {
            $currentEnd = $currentStart->copy()->endOfWeek();
            
            $data = dataSampah::where('id_user', $userId)
                ->where('status_scan', 'success')
                ->whereBetween('waktu_scan', [$currentStart, $currentEnd])
                ->select(
                    DB::raw('SUM(poin_dapat) as total_poin'),
                    DB::raw('SUM(jumlah_botol) as total_botol'),
                    DB::raw('SUM(berat_botol) as total_berat')
                )
                ->first();
            
            $labels[] = "Minggu $weekNumber";
            $poinData[] = (int) ($data->total_poin ?? 0);
            $botolData[] = (int) ($data->total_botol ?? 0);
            $beratData[] = (float) ($data->total_berat ?? 0);
            
            $weeks[] = $currentStart->format('d M') . ' - ' . $currentEnd->format('d M');
            $currentStart->addWeek();
            $weekNumber++;
        }
        
        $periodText = implode(', ', $weeks);
        
        return response()->json([
            'success' => true,
            'labels' => $labels,
            'poin_data' => $poinData,
            'botol_data' => $botolData,
            'berat_data' => $beratData,
            'period_label' => 'Mingguan: ' . $periodText
        ]);
    }
    
    private function getMonthlyChartData($userId, $startDate, $endDate)
    {
        $labels = [];
        $poinData = [];
        $botolData = [];
        $beratData = [];
        $months = [];
        
        $monthlyData = dataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->whereBetween('waktu_scan', [$startDate, $endDate])
            ->select(
                DB::raw('DATE_FORMAT(waktu_scan, "%Y-%m") as bulan'),
                DB::raw('SUM(poin_dapat) as total_poin'),
                DB::raw('SUM(jumlah_botol) as total_botol'),
                DB::raw('SUM(berat_botol) as total_berat')
            )
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get();
        
        foreach ($monthlyData as $item) {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $item->bulan);
            $labels[] = $date->format('M Y');
            $poinData[] = (int) ($item->total_poin ?? 0);
            $botolData[] = (int) ($item->total_botol ?? 0);
            $beratData[] = (float) ($item->total_berat ?? 0);
            $months[] = $date->format('M Y');
        }
        
        $periodText = !empty($months) ? $months[0] . ' s/d ' . end($months) : 'Tidak ada data';
        
        return response()->json([
            'success' => true,
            'labels' => $labels,
            'poin_data' => $poinData,
            'botol_data' => $botolData,
            'berat_data' => $beratData,
            'period_label' => 'Bulanan: ' . $periodText
        ]);
    }
    
    public function getHistory()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
        
        $histories = dataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->orderBy('id_sampah', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $histories
        ]);
    }
    
    public function getAllHistory()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
        
        $histories = dataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->orderBy('id_sampah', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $histories
        ]);
    }
    
    public function updatePoin(Request $request)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak login'
            ]);
        }
        
        $request->validate([
            'poin' => 'required|integer',
            'id_user' => 'required'
        ]);
        
        $user = akun::where('id_user', $userId)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ]);
        }
        
        $user->total_poin = ($user->total_poin ?? 0) + $request->poin;
        $user->save();
        
        session(['user_poin' => $user->total_poin]);
        
        return response()->json([
            'success' => true,
            'poin' => $user->total_poin,
            'message' => 'Poin berhasil ditambahkan'
        ]);
    }
    
    public function saveTransaksi(Request $request)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak login'
            ], 401);
        }
        
        $request->validate([
            'jumlah_botol' => 'required|integer',
            'berat_botol' => 'required|numeric',
            'poin_dapat' => 'required|integer',
        ]);
        
        DB::beginTransaction();
        
        try {
            $now = now();
            
            $dataSampah = dataSampah::create([
                'id_user' => $userId,
                'jumlah_botol' => $request->jumlah_botol,
                'berat_botol' => $request->berat_botol,
                'poin_dapat' => $request->poin_dapat,
                'status_scan' => 'success',
                'waktu_scan' => $now->toDateString(),
                'jam_scan' => $now->toTimeString()
            ]);
            
            $user = akun::where('id_user', $userId)->first();
            $user->total_poin = ($user->total_poin ?? 0) + $request->poin_dapat;
            $user->save();
            
            session(['user_poin' => $user->total_poin]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Upload berhasil!',
                'poin' => $user->total_poin
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== METHOD UNTUK RIWAYAT REWARD LENGKAP ==========

    // ========== METHOD UNTUK RIWAYAT REWARD LENGKAP (TANPA transaksi_reward) ==========

public function getAllHistoryReward()
{
    $userId = session('user_id');
    
    if (!$userId) {
        return response()->json([
            'success' => false,
            'data' => [],
            'message' => 'User tidak login'
        ]);
    }
    
    try {
        // Ambil SEMUA transaksi user
        $transaksis = \App\Models\transaksi::where('id_user', $userId)
            ->orderBy('id_transaksi', 'desc')
            ->get();
        
        $history = [];
        
        foreach ($transaksis as $transaksi) {
            $carbonDate = \Carbon\Carbon::parse($transaksi->tanggal_transaksi);
            $carbonDate->locale('id');
            $tanggalFormatted = $carbonDate->translatedFormat('j F Y');
            
            $jamFormatted = $transaksi->jam_transaksi 
                ? substr($transaksi->jam_transaksi, 0, 5) 
                : '00:00';
            
            $history[] = [
                'id' => $transaksi->id_transaksi,
                'tanggal' => $tanggalFormatted . ', ' . $jamFormatted,
                'reward' => 'Penukaran Poin',
                'jumlah' => $transaksi->jumlah_transaksi ?? 1,
                'total_poin' => $transaksi->poin_transaksi,
                'status' => $transaksi->status_transaksi ?? 'pending'
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $history,
            'total' => count($history)
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
}