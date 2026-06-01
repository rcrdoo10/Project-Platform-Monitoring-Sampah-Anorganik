<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\akun;
use App\Models\DataSampah;
use App\Models\AkunSampah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PoinController extends Controller
{
    // ============================================
    // METHOD UNTUK DASHBOARD
    // ============================================
    
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
        
        $totalBotol = DataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->sum('jumlah_botol');
        
        $totalBerat = DataSampah::where('id_user', $userId)
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
        
        $totalBotol = DataSampah::where('id_user', $userId)
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
        
        $totalBerat = DataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->sum('berat_botol');
        
        return response()->json([
            'success' => true,
            'total_berat' => $totalBerat ?? 0
        ]);
    }
    
    // ============================================
    // METHOD UNTUK GRAFIK (BERDASARKAN WAKTU SCAN USER)
    // ============================================
    
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
        
        // Ambil range tanggal dari data user yang login
        $minDate = DataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->min('waktu_scan');
        
        $maxDate = DataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->max('waktu_scan');
        
        // Jika tidak ada data
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
        
        // Sesuaikan tampilan berdasarkan rentang data
        if ($daysDiff <= 30) {
            return $this->getDailyData($userId, $startDate, $endDate);
        } elseif ($daysDiff <= 180) {
            return $this->getWeeklyChartData($userId, $startDate, $endDate);
        } else {
            return $this->getMonthlyChartData($userId, $startDate, $endDate);
        }
    }
    
    // Data per hari
    private function getDailyData($userId, $startDate, $endDate)
    {
        $labels = [];
        $poinData = [];
        $botolData = [];
        $beratData = [];
        
        $dailyData = DataSampah::where('id_user', $userId)
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
    
    // Data per minggu
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
            
            $data = DataSampah::where('id_user', $userId)
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
    
    // Data per bulan
    private function getMonthlyChartData($userId, $startDate, $endDate)
    {
        $labels = [];
        $poinData = [];
        $botolData = [];
        $beratData = [];
        $months = [];
        
        $monthlyData = DataSampah::where('id_user', $userId)
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
    
    // ============================================
    // METHOD UNTUK RIWAYAT
    // ============================================
    
    public function getHistory()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
        
        $histories = DataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->orderBy('waktu_scan', 'desc')
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
        
        $histories = DataSampah::where('id_user', $userId)
            ->where('status_scan', 'success')
            ->orderBy('waktu_scan', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $histories
        ]);
    }
    
    // ============================================
    // METHOD UNTUK UPDATE POIN
    // ============================================
    
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
    
    // ============================================
    // METHOD UNTUK SAVE TRANSAKSI
    // ============================================
    
    public function saveTransaksi(Request $request)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak login'
            ]);
        }
        
        $request->validate([
            'jumlah_botol' => 'required|integer',
            'berat_botol' => 'required|numeric',
            'poin_dapat' => 'required|integer',
            'status_scan' => 'required|string'
        ]);
        
        DB::beginTransaction();
        
        try {
            // Insert into data_sampah
            $dataSampah = DataSampah::create([
                'id_user' => $userId,
                'jumlah_botol' => $request->jumlah_botol,
                'berat_botol' => $request->berat_botol,
                'poin_dapat' => $request->poin_dapat,
                'status_scan' => $request->status_scan,
                'waktu_scan' => now()
            ]);
            
            // Update user poin
            $user = akun::where('id_user', $userId)->first();
            $user->total_poin = ($user->total_poin ?? 0) + $request->poin_dapat;
            $user->save();
            
            // Update session
            session(['user_poin' => $user->total_poin]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'poin' => $user->total_poin,
                'data' => $dataSampah
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ]);
        }
    }
}