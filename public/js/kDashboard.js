// ========== VARIABEL GLOBAL ==========
let pendingCount = 3;
let totalTransaksi = 2847;
let allHistoryData = [];
let mainChart;

// Data statis untuk grafik
const penukaranPerHari = [
    12, 8, 15, 10, 7, 14, 18, 22, 19, 25, 30, 28, 35, 32, 40, 38, 45, 42, 50,
    48, 55, 52, 60, 58, 65, 62, 70, 68, 75, 72,
];

const labels = [];
for (let i = 1; i <= 30; i++) {
    labels.push(`${i} Mei`);
}

// ========== AMBIL RIWAYAT DARI DATABASE ==========
function fetchHistoryData() {
    fetch("/api/get-all-history")
        .then((response) => response.json())
        .then((data) => {
            if (data.success && data.data) {
                allHistoryData = data.data.map((item) => ({
                    tanggal:
                        item.tanggal ||
                        item.created_at ||
                        new Date().toLocaleString(),
                    reward: item.nama_reward || item.reward || "-",
                    poin: item.poin_digunakan || item.poin || 0,
                    status: item.status || "Disetujui",
                }));
            } else {
                // Data statis sebagai fallback
                allHistoryData = [
                    {
                        tanggal: "30 Mei 2026, 18:30",
                        reward: "Paket Nasi Box",
                        poin: 500,
                        status: "Disetujui",
                    },
                    {
                        tanggal: "29 Mei 2026, 14:20",
                        reward: "Jus Buah Segar",
                        poin: 300,
                        status: "Disetujui",
                    },
                    {
                        tanggal: "28 Mei 2026, 09:45",
                        reward: "Eco Bag",
                        poin: 200,
                        status: "Disetujui",
                    },
                    {
                        tanggal: "27 Mei 2026, 16:30",
                        reward: "Kopi Spesial",
                        poin: 250,
                        status: "Disetujui",
                    },
                    {
                        tanggal: "26 Mei 2026, 11:15",
                        reward: "Tumbler",
                        poin: 800,
                        status: "Disetujui",
                    },
                ];
            }
            renderHistoryTable();
        })
        .catch((error) => {
            console.error("Error fetching history:", error);
            renderHistoryTable();
        });
}

// ========== FUNGSI RENDER TABEL ==========
function renderHistoryTable() {
    const tbody = document.getElementById("historyBody");
    if (!tbody) return;

    const recentData = allHistoryData.slice(0, 5);
    tbody.innerHTML = "";

    if (recentData.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" style="text-align:center;">Belum ada data penukaran</td></tr>';
        return;
    }

    recentData.forEach((item) => {
        const row = tbody.insertRow();
        row.insertCell(0).innerText = item.tanggal;
        row.insertCell(1).innerHTML =
            `<strong style="color:#2e7d64;">${item.reward}</strong>`;
        row.insertCell(2).innerHTML =
            `<span style="color:#e9c46a; font-weight:600;">${Number(item.poin).toLocaleString()} poin</span>`;
        row.insertCell(3).innerHTML =
            `<span class="status-badge-approved"><i class="fas fa-check-circle"></i> ${item.status}</span>`;
        row.insertCell(4).innerHTML =
            `<a href="#" class="detail-link" data-tgl="${item.tanggal}">Lihat detail</a>`;
    });

    document.querySelectorAll("#historyBody .detail-link").forEach((link) => {
        link.addEventListener("click", (e) => {
            e.preventDefault();
            const tgl = link.getAttribute("data-tgl") || "transaksi";
            showNotification(`✨ Detail penukaran reward: ${tgl}`, "info");
        });
    });
}

function renderFullHistoryTable() {
    const tbody = document.getElementById("fullHistoryBody");
    if (!tbody) return;

    tbody.innerHTML = "";

    if (allHistoryData.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" style="text-align:center;">Belum ada data penukaran</td></tr>';
        return;
    }

    allHistoryData.forEach((item) => {
        const row = tbody.insertRow();
        row.insertCell(0).innerText = item.tanggal;
        row.insertCell(1).innerHTML =
            `<strong style="color:#2e7d64;">${item.reward}</strong>`;
        row.insertCell(2).innerHTML =
            `<span style="color:#e9c46a; font-weight:600;">${Number(item.poin).toLocaleString()} poin</span>`;
        row.insertCell(3).innerHTML =
            `<span class="status-badge-approved"><i class="fas fa-check-circle"></i> ${item.status}</span>`;
        row.insertCell(4).innerHTML =
            `<a href="#" class="detail-link" data-tgl="${item.tanggal}">Lihat detail</a>`;
    });
}

// ========== FUNGSI MODAL ==========
function openFullHistoryModal() {
    renderFullHistoryTable();
    const modal = document.getElementById("fullHistoryModal");
    if (modal) {
        modal.style.display = "block";
        document.body.style.overflow = "hidden";
    }
}

function closeFullHistoryModal() {
    const modal = document.getElementById("fullHistoryModal");
    if (modal) {
        modal.style.display = "none";
        document.body.style.overflow = "";
    }
}

// ========== GRAFIK ==========
function initMainChart() {
    const canvas = document.getElementById("wasteChart");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    if (mainChart) mainChart.destroy();
    mainChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Total Penukaran",
                    data: penukaranPerHari,
                    backgroundColor: "#2e7d64",
                    borderRadius: 8,
                    borderWidth: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                tooltip: {
                    mode: "index",
                    intersect: false,
                    callbacks: {
                        label: function (context) {
                            return `Penukaran: ${context.raw} unit`;
                        },
                    },
                },
                legend: { position: "top" },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: "#e9edf2" },
                    title: {
                        display: true,
                        text: "Jumlah Penukaran (Unit)",
                        color: "#5f7c8b",
                    },
                },
                x: {
                    grid: { display: false },
                    title: {
                        display: true,
                        text: "Tanggal (Mei 2026)",
                        color: "#5f7c8b",
                    },
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

// ========== NOTIFIKASI ==========
function showNotification(message, type = "success") {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `<i class="fas ${type === "success" ? "fa-check-circle" : "fa-exclamation-circle"}"></i><span>${message}</span>`;
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${type === "success" ? "#2e7d64" : "#dc3545"};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// ========== TOGGLE NAV ==========
function toggleNav() {
    const navToggle = document.getElementById("navToggle");
    const navigation = document.getElementById("navigation");
    const overlay = document.getElementById("overlay");

    if (navToggle) navToggle.classList.toggle("active");
    if (navigation) navigation.classList.toggle("active");
    if (overlay) overlay.classList.toggle("active");
}

// ========== EVENT LISTENERS ==========
function initEventListeners() {
    const navToggle = document.getElementById("navToggle");
    const overlay = document.getElementById("overlay");
    const logoutBtn = document.getElementById("logoutBtn");
    const lihatSemuaBtn = document.getElementById("lihatSemuaRiwayatBtn");
    const fullHistoryClose = document.querySelector(".full-history-close");
    const fullHistoryModal = document.getElementById("fullHistoryModal");

    if (navToggle) navToggle.addEventListener("click", toggleNav);
    if (overlay) overlay.addEventListener("click", toggleNav);

    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            showNotification(
                "🚪 Anda telah logout dari Dompet Sadhar. Sampai jumpa kembali! 🌱",
                "info",
            );
            setTimeout(() => {
                window.location.href = "/logout";
            }, 1000);
        });
    }

    if (lihatSemuaBtn) {
        lihatSemuaBtn.addEventListener("click", openFullHistoryModal);
    }

    if (fullHistoryClose) {
        fullHistoryClose.addEventListener("click", closeFullHistoryModal);
    }

    window.addEventListener("click", (e) => {
        if (e.target === fullHistoryModal) {
            closeFullHistoryModal();
        }
    });
}

// ========== INITIALIZATION ==========
function init() {
    fetchHistoryData();
    initMainChart();
    initEventListeners();

    window.addEventListener("resize", () => {
        if (mainChart) mainChart.resize();
    });
}

// Jalankan ketika halaman siap
document.addEventListener("DOMContentLoaded", init);
