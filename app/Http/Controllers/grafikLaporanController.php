<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\dataSampah;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class grafikLaporanController extends Controller
{
    // =========================================================
    // STATS
    // =========================================================
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
            'role' => $userRole,
        ]);
    }

    // =========================================================
    // CHART DATA (API)
    // =========================================================
    public function getChartData(Request $request)
    {
        $userRole = session('user_role');
        $period = $request->get('period', 'weekly');

        $allowedRoles = ['petugas', 'psl', 'rektorat'];
        $isPetugas = in_array($userRole, $allowedRoles);

        Carbon::setLocale('id');

        if ($period == 'weekly') {
            return response()->json($this->getWeeklyData($isPetugas));
        } elseif ($period == 'monthly') {
            return response()->json($this->getMonthlyData($isPetugas));
        } else {
            return response()->json($this->getYearlyData($isPetugas));
        }
    }

    // =========================================================
    // EXPORT LAPORAN (WORD → PDF)
    // =========================================================
    public function exportDocx(Request $request)
    {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $allowedRoles = ['petugas', 'psl', 'rektorat'];
        if (!in_array(session('user_role'), $allowedRoles)) {
            return redirect('/' . session('user_role') . 'Dashboard');
        }

        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        [$start, $end] = $this->resolvePeriod($period, $startDate, $endDate);

        // ========== RINGKASAN ==========
        $totalBotol = DB::table('mencatat')
            ->join('data_sampah', 'mencatat.id_sampah', '=', 'data_sampah.id_sampah')
            ->whereBetween('mencatat.tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('data_sampah.status_scan', 'success')
            ->sum('mencatat.jumlah_botol');

        $totalBerat = DB::table('mencatat')
            ->join('data_sampah', 'mencatat.id_sampah', '=', 'data_sampah.id_sampah')
            ->whereBetween('mencatat.tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('data_sampah.status_scan', 'success')
            ->sum('data_sampah.berat_botol');

        $totalPoin = DB::table('transaksi')
            ->whereBetween('tanggal_transaksi', [$start->toDateString(), $end->toDateString()])
            ->where('status_transaksi', 1)
            ->sum('poin_transaksi');

        $totalTransaksi = DB::table('transaksi')
            ->whereBetween('tanggal_transaksi', [$start->toDateString(), $end->toDateString()])
            ->count();

        $totalUserAktif = DB::table('mencatat')
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->distinct('id_user')
            ->count('id_user');

        $totalRewardDitukar = DB::table('menukarkan')
            ->join('transaksi', 'menukarkan.id_transaksi', '=', 'transaksi.id_transaksi')
            ->whereBetween('transaksi.tanggal_transaksi', [$start->toDateString(), $end->toDateString()])
            ->where('transaksi.status_transaksi', 1)
            ->sum('menukarkan.jumlah_reward');

        // ========== DETAIL TRANSAKSI ==========
        $detailTransaksi = DB::table('transaksi')
            ->leftJoin('akun', 'transaksi.id_user', '=', 'akun.id_user')
            ->leftJoin('menukarkan', 'transaksi.id_transaksi', '=', 'menukarkan.id_transaksi')
            ->leftJoin('reward', 'menukarkan.id_reward', '=', 'reward.id_reward')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama as user_nama',
                DB::raw('GROUP_CONCAT(reward.nama_reward SEPARATOR ", ") as reward_names'),
                DB::raw('SUM(menukarkan.jumlah_reward) as jumlah_reward')
            )
            ->whereBetween('transaksi.tanggal_transaksi', [$start->toDateString(), $end->toDateString()])
            ->groupBy(
                'transaksi.id_transaksi',
                'transaksi.tanggal_transaksi',
                'transaksi.jam_transaksi',
                'transaksi.poin_transaksi',
                'transaksi.status_transaksi',
                'akun.id_user',
                'akun.nama'
            )
            ->orderBy('transaksi.tanggal_transaksi', 'DESC')
            ->get();

        // ========== GRAFIK ==========
        $isPetugas = true;

        $dataMingguan = $this->getWeeklyData($isPetugas);
        $dataBulanan  = $this->getMonthlyData($isPetugas);
        $dataTahunan  = $this->getYearlyData($isPetugas);

        $chartMingguan = $this->generateChartPng(
            $dataMingguan['labels'] ?? [],
            $dataMingguan['botol_data'] ?? [],
            $dataMingguan['berat_data'] ?? [],
            'Grafik Mingguan'
        );

        $chartBulanan = $this->generateChartPng(
            $dataBulanan['labels'] ?? [],
            $dataBulanan['botol_data'] ?? [],
            $dataBulanan['berat_data'] ?? [],
            'Grafik Bulanan'
        );

        $chartTahunan = $this->generateChartPng(
            $dataTahunan['labels'] ?? [],
            $dataTahunan['botol_data'] ?? [],
            $dataTahunan['berat_data'] ?? [],
            'Grafik Tahunan'
        );

        // ========== LOAD TEMPLATE ==========
        $templatePath = storage_path('app/templates/template_laporan_monitoring.docx');

        if (!file_exists($templatePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Template tidak ditemukan di storage/app/templates/',
            ], 404);
        }

        $template = new TemplateProcessor($templatePath);

        // ========== ISI PLACEHOLDER ==========
        $template->setValue('bulan', $start->translatedFormat('F'));
        $template->setValue('tahun', $start->format('Y'));
        $template->setValue('nama_pelapor', session('user_nama', 'Petugas'));
        $template->setValue('id_pelapor', session('user_id', '-'));

        $template->setValue('total_botol', number_format($totalBotol, 0, ',', '.'));
        $template->setValue('total_berat', number_format($totalBerat, 2, ',', '.'));
        $template->setValue('total_poin', number_format($totalPoin, 0, ',', '.'));
        $template->setValue('total_transaksi', number_format($totalTransaksi, 0, ',', '.'));
        $template->setValue('total_user_aktif', number_format($totalUserAktif, 0, ',', '.'));
        $template->setValue('total_reward_ditukar', number_format($totalRewardDitukar, 0, ',', '.'));

        // Detail transaksi
        if ($detailTransaksi->count() > 0) {
            $template->cloneRow('no', $detailTransaksi->count());
            $i = 1;
            foreach ($detailTransaksi as $trx) {
                $tanggal = Carbon::parse($trx->tanggal_transaksi)->translatedFormat('j M Y');
                $jam = $trx->jam_transaksi ? substr($trx->jam_transaksi, 0, 5) : '00:00';
                $status = $trx->status_transaksi == 0 ? 'Pending' : 'Disetujui';

                $template->setValue("no#{$i}", $i);
                $template->setValue("tanggal_jam#{$i}", $tanggal . ' ' . $jam);
                $template->setValue("id_user#{$i}", $trx->id_user);
                $template->setValue("nama_user#{$i}", $trx->user_nama ?? '-');
                $template->setValue("reward#{$i}", $trx->reward_names ?? 'Penukaran Poin');
                $template->setValue("jumlah#{$i}", $trx->jumlah_reward ?? 1);
                $template->setValue("status#{$i}", $status);
                $i++;
            }
        } else {
            $template->setValue('no', '-');
            $template->setValue('tanggal_jam', '-');
            $template->setValue('id_user', '-');
            $template->setValue('nama_user', 'Belum ada transaksi');
            $template->setValue('reward', '-');
            $template->setValue('jumlah', '-');
            $template->setValue('status', '-');
        }

        // Sisipkan grafik
        if ($chartTahunan && file_exists($chartTahunan)) {
            $template->setImageValue('grafik_tahunan', [
                'path' => $chartTahunan,
                'width' => 450,
                'height' => 250,
            ]);
        } else {
            $template->setValue('grafik_tahunan', 'Data grafik tidak tersedia');
        }

        if ($chartBulanan && file_exists($chartBulanan)) {
            $template->setImageValue('grafik_bulanan', [
                'path' => $chartBulanan,
                'width' => 450,
                'height' => 250,
            ]);
        } else {
            $template->setValue('grafik_bulanan', 'Data grafik tidak tersedia');
        }

        if ($chartMingguan && file_exists($chartMingguan)) {
            $template->setImageValue('grafik_mingguan', [
                'path' => $chartMingguan,
                'width' => 450,
                'height' => 250,
            ]);
        } else {
            $template->setValue('grafik_mingguan', 'Data grafik tidak tersedia');
        }

        // Kesimpulan
        $template->setValue('kesimpulan_botol', number_format($totalBotol, 0, ',', '.'));
        $template->setValue('kesimpulan_berat', number_format($totalBerat, 2, ',', '.'));
        $template->setValue('kesimpulan_user', number_format($totalUserAktif, 0, ',', '.'));
        $template->setValue('kesimpulan_poin', number_format($totalPoin, 0, ',', '.'));
        $template->setValue('kesimpulan_reward', number_format($totalRewardDitukar, 0, ',', '.'));

        // ========== SIMPAN DOCX TEMPORARY ==========
        $baseName = 'Laporan_Monitoring_' . $start->format('Ymd') . '_' . $end->format('Ymd');
        $docxPath = storage_path('app/public/' . $baseName . '.docx');
        $template->saveAs($docxPath);

        // ========== CONVERT KE PDF VIA LIBREOFFICE ==========
        $sofficePath = 'C:\Program Files\LibreOffice\program\soffice.exe';

        if (!file_exists($sofficePath)) {
            @unlink($docxPath);
            if ($chartTahunan) @unlink($chartTahunan);
            if ($chartBulanan) @unlink($chartBulanan);
            if ($chartMingguan) @unlink($chartMingguan);

            return response()->json([
                'success' => false,
                'message' => 'LibreOffice tidak ditemukan di ' . $sofficePath,
            ], 500);
        }

        $pdfPath = storage_path('app/public/' . $baseName . '.pdf');

        if (file_exists($pdfPath)) {
            @unlink($pdfPath);
        }

        $cmd = sprintf(
            '"%s" --headless --convert-to pdf --outdir "%s" "%s" 2>&1',
            $sofficePath,
            str_replace('/', '\\', dirname($pdfPath)),
            str_replace('/', '\\', $docxPath)
        );

        exec($cmd, $output, $returnCode);

        // Hapus file temporary
        @unlink($docxPath);
        if ($chartTahunan) @unlink($chartTahunan);
        if ($chartBulanan) @unlink($chartBulanan);
        if ($chartMingguan) @unlink($chartMingguan);

        if (!file_exists($pdfPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal convert ke PDF',
                'output' => $output,
                'code' => $returnCode,
                'cmd' => $cmd,
            ], 500);
        }

        // ========== DOWNLOAD PDF ==========
        return response()->download($pdfPath, $baseName . '.pdf')->deleteFileAfterSend(true);
    }

    // =========================================================
    // GENERATE CHART PNG VIA QUICKCHART
    // =========================================================
    private function generateChartPng($labels, $botolData, $beratData, $title = 'Grafik')
    {
        if (empty($labels)) {
            return null;
        }

        $config = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Botol',
                        'data' => $botolData,
                        'backgroundColor' => '#52b788',
                        'borderRadius' => 8,
                    ],
                    [
                        'label' => 'Berat (kg)',
                        'data' => $beratData,
                        'backgroundColor' => '#e9c46a',
                        'borderRadius' => 8,
                    ],
                ],
            ],
            'options' => [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => $title,
                        'font' => ['size' => 16],
                    ],
                    'legend' => ['position' => 'top'],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true],
                ],
            ],
        ];

        $encoded = urlencode(json_encode($config));
        $url = "https://quickchart.io/chart?width=700&height=400&c={$encoded}";

        $tempPath = storage_path('app/public/chart_' . uniqid() . '.png');

        try {
            $image = @file_get_contents($url);
            if ($image === false) {
                \Log::warning('QuickChart gagal generate: ' . $title);
                return null;
            }
            file_put_contents($tempPath, $image);
            return $tempPath;
        } catch (\Exception $e) {
            \Log::error('Error generate chart: ' . $e->getMessage());
            return null;
        }
    }

    // =========================================================
    // RESOLVE PERIOD
    // =========================================================
    private function resolvePeriod($period, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return [Carbon::parse($startDate), Carbon::parse($endDate)];
        }

        switch ($period) {
            case 'daily':
                return [Carbon::today(), Carbon::today()];
            case 'weekly':
                return [Carbon::now()->subWeeks(4)->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'monthly':
                return [Carbon::now()->subMonths(12)->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'yearly':
                return [Carbon::now()->subYears(5)->startOfYear(), Carbon::now()->endOfYear()];
            default:
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        }
    }

    // =========================================================
    // WEEKLY DATA
    // =========================================================
    public function getWeeklyData($isPetugas = false)
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
                'period_label' => 'Belum ada data scan',
            ];
        }

        $endDate = Carbon::parse($lastDate)->endOfWeek();

        for ($i = 3; $i >= 0; $i--) {
            $weekEnd = $endDate->copy()->subWeeks($i);
            $weekStart = $weekEnd->copy()->startOfWeek();

            $weekLabels[] = 'Minggu ' . (4 - $i);

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
            'period_label' => 'Mingguan: ' . implode(', ', $weeks),
        ];
    }

    // =========================================================
    // MONTHLY DATA
    // =========================================================
    public function getMonthlyData($isPetugas = false)
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
                'period_label' => 'Belum ada data scan',
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
            'period_label' => 'Bulanan: ' . ($months[0] ?? '') . ' s/d ' . (end($months) ?? ''),
        ];
    }

    // =========================================================
    // YEARLY DATA
    // =========================================================
    public function getYearlyData($isPetugas = false)
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
                'period_label' => 'Belum ada data scan',
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
            'period_label' => 'Tahunan: ' . $minYear . ' s/d ' . $maxYear,
        ];
    }
}