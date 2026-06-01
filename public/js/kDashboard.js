// Hapus atau comment variable static:
// let totalReward = 1250;
// let pendingCount = 3;
// let totalTransaksi = 2847;

// Ganti dengan variable dinamis
let totalReward = 0;
let pendingCount = 0;
let totalTransaksi = 0;

// Fungsi untuk load data dari server
function loadDataFromServer() {
    $.ajax({
        url: "/api/reward/dashboard-data",
        type: "GET",
        dataType: "json",
        success: function (response) {
            if (response.success) {
                totalReward = response.totalReward;
                pendingCount = response.pendingCount;
                totalTransaksi = response.totalTransaksi;

                updateDashboardNumbers();
                saveData(); // Simpan ke localStorage sebagai backup
            }
        },
        error: function (xhr) {
            console.error("Error loading data:", xhr);
            // Fallback ke localStorage jika server error
            loadData();
        },
    });
}

// Fungsi untuk load riwayat transaksi dari server
function loadHistoryFromServer() {
    $.ajax({
        url: "/api/get-all-history", // Gunakan API yang sudah ada
        type: "GET",
        dataType: "json",
        success: function (response) {
            if (response.success && response.data) {
                // Konversi data dari server ke format yang digunakan
                allHistoryData = response.data.map((item) => ({
                    tanggal: item.created_at || item.tanggal,
                    reward: item.nama_reward || item.reward,
                    poin: item.poin_digunakan || item.poin,
                    status: item.status || "Disetujui",
                }));

                renderHistoryTable();
                saveData();
            }
        },
        error: function (xhr) {
            console.error("Error loading history:", xhr);
            // Fallback ke data static
            renderHistoryTable();
        },
    });
}

// Update fungsi loadData()
function loadData() {
    // Coba load dari localStorage dulu
    const savedReward = localStorage.getItem("totalReward");
    const savedPending = localStorage.getItem("pendingCount");
    const savedTransaksi = localStorage.getItem("totalTransaksi");
    const savedHistory = localStorage.getItem("allHistoryData");

    if (savedReward) totalReward = parseInt(savedReward);
    if (savedPending) pendingCount = parseInt(savedPending);
    if (savedTransaksi) totalTransaksi = parseInt(savedTransaksi);
    if (savedHistory) allHistoryData = JSON.parse(savedHistory);

    updateDashboardNumbers();

    // Load data fresh dari server
    loadDataFromServer();
    loadHistoryFromServer();
}

// Update fungsi init()
function init() {
    loadData(); // Ini akan memanggil loadDataFromServer juga
    renderHistoryTable();
    initMainChart();
    initEventListeners();

    window.addEventListener("resize", () => {
        if (mainChart) mainChart.resize();
    });
}

// Optional: Fungsi untuk refresh data secara periodik
function startAutoRefresh() {
    setInterval(() => {
        loadDataFromServer();
        loadHistoryFromServer();
    }, 30000); // Refresh setiap 30 detik
}

// Panggil auto refresh setelah init
// startAutoRefresh();
