<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataSampah;
use Illuminate\Support\Facades\DB;

class grafikLaporanController extends Controller
{
    // Method untuk mendapatkan total botol dan berat
    public function getStats()
    {
        $userRole = session('user_role');
        $userId = session('user_id');
        
        $allowedRoles = ['petugas', 'psl', 'rektorat'];
        
        if (in_array($userRole, $allowedRoles)) {
            // Petugas: ambil SEMUA data
            $totalBotol = DataSampah::where('status_scan', 'success')->sum('jumlah_botol');
            $totalBerat = DataSampah::where('status_scan', 'success')->sum('berat_botol');
        } else {
            // Warga: ambil data sendiri
            $totalBotol = DataSampah::where('id_user', $userId)
                ->where('status_scan', 'success')
                ->sum('jumlah_botol');
            
            $totalBerat = DataSampah::where('id_user', $userId)
                ->where('status_scan', 'success')
                ->sum('berat_botol');
        }
        
        return response()->json([
            'success' => true,
            'total_botol' => (int) ($totalBotol ?? 0),
            'total_berat' => (float) ($totalBerat ?? 0),
            'role' => $userRole
        ]);
    }
    
    // Method untuk mendapatkan data grafik
    public function getChartData(Request $request)
    {
        $userRole = session('user_role');
        $period = $request->get('period', 'weekly');
        
        $allowedRoles = ['petugas', 'psl', 'rektorat'];
        $isPetugas = in_array($userRole, $allowedRoles);
        
        switch ($period) {
            case 'weekly':
                $data = $this->getWeeklyData($isPetugas);
                break;
            case 'monthly':
                $data = $this->getMonthlyData($isPetugas);
                break;
            case 'yearly':
                $data = $this->getYearlyData($isPetugas);
                break;
            default:
                $data = $this->getWeeklyData($isPetugas);
        }
        
        return response()->json([
            'success' => true,
            'labels' => $data['labels'],
            'botol_data' => $data['botol'],
            'berat_data' => $data['berat'],
            'period_label' => $data['label_text']
        ]);
    }
    
    // Data Mingguan (4 minggu terakhir dari data terakhir)
    private function getWeeklyData($isPetugas = false)
    {
        $userId = session('user_id');
        $weeklyBotol = [];
        $weeklyBerat = [];
        $weekLabels = [];
        $weeks = [];
        
        // Ambil tanggal terakhir dari data
        $queryLastDate = DataSampah::where('status_scan', 'success');
        if (!$isPetugas) {
            $queryLastDate->where('id_user', $userId);
        }
        $lastDate = $queryLastDate->max('waktu_scan');
        
        // Jika tidak ada data
        if (!$lastDate) {
            return [
                'labels' => ['Tidak ada data'],
                'botol' => [0],
                'berat' => [0],
                'label_text' => 'Belum ada data scan'
            ];
        }
        
        $endDate = \Carbon\Carbon::parse($lastDate)->endOfWeek();
        
        for ($i = 3; $i >= 0; $i--) {
            $weekEnd = $endDate->copy()->subWeeks($i);
            $weekStart = $weekEnd->copy()->startOfWeek();
            
            $weekLabels[] = "Minggu " . (4 - $i);
            
            $query = DataSampah::where('status_scan', 'success')
                ->whereBetween('waktu_scan', [$weekStart, $weekEnd]);
            
            if (!$isPetugas) {
                $query->where('id_user', $userId);
            }
            
            $data = $query->select(
                DB::raw('COALESCE(SUM(jumlah_botol), 0) as total_botol'),
                DB::raw('COALESCE(SUM(berat_botol), 0) as total_berat')
            )->first();
            
            $weeklyBotol[] = (int) ($data->total_botol ?? 0);
            $weeklyBerat[] = (float) ($data->total_berat ?? 0);
            
            $weeks[] = $weekStart->format('d M') . '-' . $weekEnd->format('d M');
        }
        
        return [
            'labels' => $weekLabels,
            'botol' => $weeklyBotol,
            'berat' => $weeklyBerat,
            'label_text' => 'Mingguan: ' . implode(', ', $weeks)
        ];
    }
    
    // Data Bulanan (berdasarkan data yang ada)
    private function getMonthlyData($isPetugas = false)
    {
        $userId = session('user_id');
        $monthlyBotol = [];
        $monthlyBerat = [];
        $monthLabels = [];
        $months = [];
        
        // Ambil range tanggal dari data yang ada
        $queryMin = DataSampah::where('status_scan', 'success');
        $queryMax = DataSampah::where('status_scan', 'success');
        
        if (!$isPetugas) {
            $queryMin->where('id_user', $userId);
            $queryMax->where('id_user', $userId);
        }
        
        $minDate = $queryMin->min('waktu_scan');
        $maxDate = $queryMax->max('waktu_scan');
        
        // Jika tidak ada data
        if (!$minDate || !$maxDate) {
            return [
                'labels' => ['Tidak ada data'],
                'botol' => [0],
                'berat' => [0],
                'label_text' => 'Belum ada data scan'
            ];
        }
        
        $startDate = \Carbon\Carbon::parse($minDate)->startOfMonth();
        $endDate = \Carbon\Carbon::parse($maxDate)->endOfMonth();
        $monthsCount = $startDate->diffInMonths($endDate) + 1;
        
        // Batasi maksimal 12 bulan
        $monthsCount = min($monthsCount, 12);
        $startDate = $endDate->copy()->subMonths($monthsCount - 1)->startOfMonth();
        
        for ($i = 0; $i < $monthsCount; $i++) {
            $monthStart = $startDate->copy()->addMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            $monthLabels[] = $monthStart->format('M Y');
            
            $query = DataSampah::where('status_scan', 'success')
                ->whereBetween('waktu_scan', [$monthStart, $monthEnd]);
            
            if (!$isPetugas) {
                $query->where('id_user', $userId);
            }
            
            $data = $query->select(
                DB::raw('COALESCE(SUM(jumlah_botol), 0) as total_botol'),
                DB::raw('COALESCE(SUM(berat_botol), 0) as total_berat')
            )->first();
            
            $monthlyBotol[] = (int) ($data->total_botol ?? 0);
            $monthlyBerat[] = (float) ($data->total_berat ?? 0);
            $months[] = $monthStart->format('M Y');
        }
        
        return [
            'labels' => $monthLabels,
            'botol' => $monthlyBotol,
            'berat' => $monthlyBerat,
            'label_text' => 'Bulanan: ' . ($months[0] ?? '') . ' s/d ' . (end($months) ?? '')
        ];
    }
    
    // Data Tahunan (berdasarkan data yang ada)
    private function getYearlyData($isPetugas = false)
    {
        $userId = session('user_id');
        $yearlyBotol = [];
        $yearlyBerat = [];
        $yearLabels = [];
        
        // Ambil range tahun dari data yang ada
        $queryMin = DataSampah::where('status_scan', 'success');
        $queryMax = DataSampah::where('status_scan', 'success');
        
        if (!$isPetugas) {
            $queryMin->where('id_user', $userId);
            $queryMax->where('id_user', $userId);
        }
        
        $minYear = $queryMin->select(DB::raw('YEAR(MIN(waktu_scan)) as tahun'))->value('tahun');
        $maxYear = $queryMax->select(DB::raw('YEAR(MAX(waktu_scan)) as tahun'))->value('tahun');
        
        // Jika tidak ada data
        if (!$minYear || !$maxYear) {
            return [
                'labels' => ['Tidak ada data'],
                'botol' => [0],
                'berat' => [0],
                'label_text' => 'Belum ada data scan'
            ];
        }
        
        for ($year = $minYear; $year <= $maxYear; $year++) {
            $yearLabels[] = $year;
            
            $startDate = \Carbon\Carbon::create($year, 1, 1)->startOfYear();
            $endDate = \Carbon\Carbon::create($year, 12, 31)->endOfYear();
            
            $query = DataSampah::where('status_scan', 'success')
                ->whereBetween('waktu_scan', [$startDate, $endDate]);
            
            if (!$isPetugas) {
                $query->where('id_user', $userId);
            }
            
            $data = $query->select(
                DB::raw('COALESCE(SUM(jumlah_botol), 0) as total_botol'),
                DB::raw('COALESCE(SUM(berat_botol), 0) as total_berat')
            )->first();
            
            $yearlyBotol[] = (int) ($data->total_botol ?? 0);
            $yearlyBerat[] = (float) ($data->total_berat ?? 0);
        }
        
        return [
            'labels' => $yearLabels,
            'botol' => $yearlyBotol,
            'berat' => $yearlyBerat,
            'label_text' => 'Tahunan: ' . $minYear . ' s/d ' . $maxYear
        ];
    }
}