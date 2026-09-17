<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\dataSampah;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class grafikLaporanController extends Controller
{
    public function getStats()
    {
        $userRole = session('user_role');
        $userId = session('user_id');
        
        $allowedRoles = ['petugas', 'psl', 'rektorat'];
        
        if (in_array($userRole, $allowedRoles)) {
            $totalBotol = dataSampah::where('status_scan', 'success')->sum('jumlah_botol');
            $totalBerat = dataSampah::where('status_scan', 'success')->sum('berat_botol');
        } else {
            $totalBotol = dataSampah::where('id_user', $userId)
                ->where('status_scan', 'success')
                ->sum('jumlah_botol');
            
            $totalBerat = dataSampah::where('id_user', $userId)
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
    
    public function getChartData(Request $request)
{
    $userRole = session('user_role');
    $userId = session('user_id');
    $period = $request->get('period', 'weekly');
    
    $allowedRoles = ['petugas', 'psl', 'rektorat'];
    $isPetugas = in_array($userRole, $allowedRoles);
    
    Carbon::setLocale('id');
    
    // Panggil method sesuai period
    if ($period == 'weekly') {
        return response()->json($this->getWeeklyData($isPetugas));
    } elseif ($period == 'monthly') {
        return response()->json($this->getMonthlyData($isPetugas));
    } else {
        return response()->json($this->getYearlyData($isPetugas));
    }
}
    
    private function getWeeklyData($isPetugas = false)
{
    $userId = session('user_id');
    $weeklyBotol = [];
    $weeklyBerat = [];
    $weekLabels = [];
    $weeks = [];
    
    $queryLastDate = dataSampah::where('status_scan', 'success');
    if (!$isPetugas) {
        $queryLastDate->where('id_user', $userId);
    }
    $lastDate = $queryLastDate->max('waktu_scan');
    
    if (!$lastDate) {
        return [
            'success' => true,
            'labels' => ['Tidak ada data'],
            'botol_data' => [0],
            'berat_data' => [0],
            'period_label' => 'Belum ada data scan'
        ];
    }
    
    $endDate = Carbon::parse($lastDate)->endOfWeek();
    
    for ($i = 3; $i >= 0; $i--) {
        $weekEnd = $endDate->copy()->subWeeks($i);
        $weekStart = $weekEnd->copy()->startOfWeek();
        
        $weekLabels[] = "Minggu " . (4 - $i);
        
        $query = dataSampah::where('status_scan', 'success')
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
        'success' => true,
        'labels' => $weekLabels,
        'botol_data' => $weeklyBotol,
        'berat_data' => $weeklyBerat,
        'period_label' => 'Mingguan: ' . implode(', ', $weeks)
    ];
}
    
    private function getMonthlyData($isPetugas = false)
{
    $userId = session('user_id');
    $monthlyBotol = [];
    $monthlyBerat = [];
    $monthLabels = [];
    $months = [];
    
    $queryMin = dataSampah::where('status_scan', 'success');
    $queryMax = dataSampah::where('status_scan', 'success');
    
    if (!$isPetugas) {
        $queryMin->where('id_user', $userId);
        $queryMax->where('id_user', $userId);
    }
    
    $minDate = $queryMin->min('waktu_scan');
    $maxDate = $queryMax->max('waktu_scan');
    
    if (!$minDate || !$maxDate) {
        return [
            'success' => true,
            'labels' => ['Tidak ada data'],
            'botol_data' => [0],
            'berat_data' => [0],
            'period_label' => 'Belum ada data scan'
        ];
    }
    
    $startDate = Carbon::parse($minDate)->startOfMonth();
    $endDate = Carbon::parse($maxDate)->endOfMonth();
    $monthsCount = $startDate->diffInMonths($endDate) + 1;
    $monthsCount = min($monthsCount, 12);
    $startDate = $endDate->copy()->subMonths($monthsCount - 1)->startOfMonth();
    
    for ($i = 0; $i < $monthsCount; $i++) {
        $monthStart = $startDate->copy()->addMonths($i);
        $monthEnd = $monthStart->copy()->endOfMonth();
        
        $monthLabels[] = $monthStart->translatedFormat('M Y');
        
        $query = dataSampah::where('status_scan', 'success')
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
        $months[] = $monthStart->translatedFormat('M Y');
    }
    
    return [
        'success' => true,
        'labels' => $monthLabels,
        'botol_data' => $monthlyBotol,
        'berat_data' => $monthlyBerat,
        'period_label' => 'Bulanan: ' . ($months[0] ?? '') . ' s/d ' . (end($months) ?? '')
    ];
}
    
    private function getYearlyData($isPetugas = false)
{
    $userId = session('user_id');
    $yearlyBotol = [];
    $yearlyBerat = [];
    $yearLabels = [];
    
    $queryMin = dataSampah::where('status_scan', 'success');
    $queryMax = dataSampah::where('status_scan', 'success');
    
    if (!$isPetugas) {
        $queryMin->where('id_user', $userId);
        $queryMax->where('id_user', $userId);
    }
    
    $minYear = $queryMin->select(DB::raw('YEAR(MIN(waktu_scan)) as tahun'))->value('tahun');
    $maxYear = $queryMax->select(DB::raw('YEAR(MAX(waktu_scan)) as tahun'))->value('tahun');
    
    if (!$minYear || !$maxYear) {
        return [
            'success' => true,
            'labels' => ['Tidak ada data'],
            'botol_data' => [0],
            'berat_data' => [0],
            'period_label' => 'Belum ada data scan'
        ];
    }
    
    for ($year = $minYear; $year <= $maxYear; $year++) {
        $yearLabels[] = $year;
        
        $startDate = Carbon::create($year, 1, 1)->startOfYear();
        $endDate = Carbon::create($year, 12, 31)->endOfYear();
        
        $query = dataSampah::where('status_scan', 'success')
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
        'success' => true,
        'labels' => $yearLabels,
        'botol_data' => $yearlyBotol,
        'berat_data' => $yearlyBerat,
        'period_label' => 'Tahunan: ' . $minYear . ' s/d ' . $maxYear
    ];
}
}