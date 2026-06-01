// ============================================
// wDashboard.js - Grafik menyesuaikan waktu scan user
// ============================================

let totalPoin = window.initialData?.user_poin || 0;
let totalBotol = window.initialData?.total_botol || 0;
let totalBerat = window.initialData?.total_berat || 0;
let mainChart = null;
let allHistoryData = [];

// ============================================
// FUNGSI UPDATE TAMPILAN
// ============================================

function updateDashboardNumbers() {
    const poinElement = document.getElementById("totalPoinVal");
    const botolElement = document.getElementById("totalBotolVal");
    const beratElement = document.getElementById("totalBeratVal");

    if (poinElement) {
        poinElement.innerText = totalPoin.toLocaleString("id-ID");
    }
    if (botolElement) {
        botolElement.innerText = totalBotol.toLocaleString("id-ID");
    }
    if (beratElement) {
        beratElement.innerText = totalBerat.toLocaleString("id-ID");
    }
}

// ============================================
// FUNGSI GRAFIK (OTOMATIS MENYESUAIKAN WAKTU SCAN)
// ============================================

async function loadChart() {
    try {
        const response = await fetch("/api/chart-stats");
        const data = await response.json();

        if (data.success) {
            createChart(
                data.labels,
                data.poin_data,
                data.botol_data,
                data.berat_data,
            );

            // Update periode label
            const periodLabelDiv = document.querySelector(
                '.chart-fullwidth div[style*="font-size:12px"]',
            );
            if (periodLabelDiv && data.period_label) {
                periodLabelDiv.innerHTML = data.period_label;
            }
        } else {
            createEmptyChart();
        }
    } catch (error) {
        console.error("Error loading chart:", error);
        createEmptyChart();
    }
}

function createChart(labels, poinData, botolData, beratData) {
    const ctx = document.getElementById("wasteChart");
    if (!ctx) return;

    if (mainChart) mainChart.destroy();

    if (
        labels.length === 0 ||
        (labels.length === 1 && labels[0] === "Belum ada data")
    ) {
        labels = ["Belum ada data"];
        poinData = [0];
        botolData = [0];
        beratData = [0];
    }

    mainChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Poin",
                    data: poinData,
                    borderColor: "#2e7d64",
                    backgroundColor: "rgba(46, 125, 100, 0.1)",
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: "#2e7d64",
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: "Botol",
                    data: botolData,
                    borderColor: "#52b788",
                    backgroundColor: "rgba(82, 183, 136, 0.1)",
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: "#52b788",
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: "Berat (kg)",
                    data: beratData,
                    borderColor: "#e9c46a",
                    backgroundColor: "rgba(233, 196, 106, 0.1)",
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: "#e9c46a",
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: "index",
                intersect: false,
            },
            plugins: {
                tooltip: {
                    mode: "index",
                    intersect: false,
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || "";
                            let value = context.raw;
                            if (context.dataset.label === "Berat (kg)") {
                                return (
                                    label +
                                    ": " +
                                    value.toLocaleString("id-ID") +
                                    " kg"
                                );
                            }
                            return label + ": " + value.toLocaleString("id-ID");
                        },
                    },
                },
                legend: {
                    position: "top",
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: "#e9edf2" },
                    title: {
                        display: true,
                        text: "Jumlah",
                        color: "#5f7c8b",
                    },
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 10,
                    },
                },
            },
        },
    });
}

function createEmptyChart() {
    createChart(["Tidak ada data"], [0], [0], [0]);
}

// ============================================
// FUNGSI RIWAYAT
// ============================================

async function getHistory() {
    try {
        const response = await fetch("/api/get-history", {
            headers: { Accept: "application/json" },
        });

        const data = await response.json();

        if (data.success && data.data.length > 0) {
            allHistoryData = data.data.map((item) => ({
                tanggal: formatTanggal(item.waktu_scan),
                botol: item.jumlah_botol + " Botol",
                berat: item.berat_botol + " kg",
                poin: "+" + item.poin_dapat,
                status:
                    item.status_scan === "success" ? "Berhasil" : "Diproses",
            }));
        }

        renderHistoryTable();
    } catch (error) {
        console.error("Error getting history:", error);
        allHistoryData = [];
        renderHistoryTable();
    }
}

async function getAllHistory() {
    try {
        const response = await fetch("/api/get-all-history", {
            headers: { Accept: "application/json" },
        });

        const data = await response.json();

        if (data.success && data.data.length > 0) {
            allHistoryData = data.data.map((item) => ({
                tanggal: formatTanggal(item.waktu_scan),
                botol: item.jumlah_botol + " Botol",
                berat: item.berat_botol + " kg",
                poin: "+" + item.poin_dapat,
                status:
                    item.status_scan === "success" ? "Berhasil" : "Diproses",
            }));
        }

        renderFullHistoryTable();
    } catch (error) {
        console.error("Error getting all history:", error);
    }
}

function formatTanggal(waktuScan) {
    if (!waktuScan) return "-";
    let d = new Date(waktuScan);
    return d.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function renderHistoryTable() {
    const tbody = document.getElementById("historyBody");
    if (!tbody) return;

    const recentData = allHistoryData.slice(0, 5);
    tbody.innerHTML = "";

    if (recentData.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="6" style="text-align:center;">Belum ada riwayat scan</td></tr>';
        return;
    }

    recentData.forEach((item) => {
        const row = tbody.insertRow();
        row.insertCell(0).innerText = item.tanggal;
        row.insertCell(1).innerText = item.botol;
        row.insertCell(2).innerText = item.berat;
        row.insertCell(3).innerHTML =
            `<strong style="color:#2b7a5c;">${item.poin}</strong>`;
        row.insertCell(4).innerHTML =
            `<span class="status-badge"><i class="fas fa-check-circle"></i> ${item.status}</span>`;
        row.insertCell(5).innerHTML =
            `<a href="#" class="detail-link" onclick="showDetail('${item.tanggal}')">Lihat detail</a>`;
    });
}

function renderFullHistoryTable() {
    const tbody = document.getElementById("fullHistoryBody");
    if (!tbody) return;

    tbody.innerHTML = "";

    if (allHistoryData.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="6" style="text-align:center;">Belum ada riwayat scan</td></tr>';
        return;
    }

    allHistoryData.forEach((item) => {
        const row = tbody.insertRow();
        row.insertCell(0).innerText = item.tanggal;
        row.insertCell(1).innerText = item.botol;
        row.insertCell(2).innerText = item.berat;
        row.insertCell(3).innerHTML =
            `<strong style="color:#2b7a5c;">${item.poin}</strong>`;
        row.insertCell(4).innerHTML =
            `<span class="status-badge"><i class="fas fa-check-circle"></i> ${item.status}</span>`;
        row.insertCell(5).innerHTML =
            `<a href="#" class="detail-link" onclick="showDetail('${item.tanggal}')">Lihat detail</a>`;
    });
}

function showDetail(tanggal) {
    alert(`Detail transaksi: ${tanggal}`);
}

// ============================================
// FUNGSI MODAL
// ============================================

const fullHistoryModal = document.getElementById("fullHistoryModal");
const lihatSemuaBtn = document.getElementById("lihatSemuaRiwayatBtn");
const fullHistoryClose = document.querySelector(".full-history-close");

function openFullHistoryModal() {
    getAllHistory();
    if (fullHistoryModal) fullHistoryModal.style.display = "block";
    document.body.style.overflow = "hidden";
}

function closeFullHistoryModal() {
    if (fullHistoryModal) fullHistoryModal.style.display = "none";
    document.body.style.overflow = "";
}

// ============================================
// FUNGSI NAVIGASI
// ============================================

const navToggle = document.getElementById("navToggle");
const navigation = document.getElementById("navigation");
const overlay = document.getElementById("overlay");

function toggleNav() {
    if (navToggle) navToggle.classList.toggle("active");
    if (navigation) navigation.classList.toggle("active");
    if (overlay) overlay.classList.toggle("active");
}

// ============================================
// INISIALISASI
// ============================================

async function init() {
    updateDashboardNumbers();
    await loadChart();
    await getHistory();

    if (navToggle) navToggle.addEventListener("click", toggleNav);
    if (overlay) overlay.addEventListener("click", toggleNav);
    if (lihatSemuaBtn)
        lihatSemuaBtn.addEventListener("click", openFullHistoryModal);
    if (fullHistoryClose)
        fullHistoryClose.addEventListener("click", closeFullHistoryModal);

    window.addEventListener("click", (e) => {
        if (e.target === fullHistoryModal) closeFullHistoryModal();
    });

    window.addEventListener("resize", () => {
        if (mainChart) mainChart?.resize();
    });
}

document.addEventListener("DOMContentLoaded", init);
