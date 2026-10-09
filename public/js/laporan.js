let currentPeriod = "weekly";
let mainChart = null;

console.log("Data from server:", window.initialData);

/* =====================================================
   FETCH DATA GRAFIK
   ===================================================== */
async function fetchChartData(period) {
    try {
        const response = await fetch(`/api/laporan/chart?period=${period}`);
        const data = await response.json();

        if (data.success) {
            updateChart(data.labels, data.botol_data, data.berat_data);

            const periodLabel = document.getElementById("periodLabel");
            if (periodLabel) {
                periodLabel.innerText = `Periode: ${data.period_label}`;
            }
        }
    } catch (error) {
        console.error("Error fetching chart data:", error);
        showNotification("Gagal memuat data grafik", "error");
    }
}

function updateChart(labels, botolData, beratData) {
    if (mainChart) {
        mainChart.data.labels = labels;
        mainChart.data.datasets[0].data = botolData;
        mainChart.data.datasets[1].data = beratData;
        mainChart.update();
    } else {
        createChart(labels, botolData, beratData);
    }
}

function createChart(labels, botolData, beratData) {
    const ctx = document.getElementById("wasteChart");
    if (!ctx) return;

    if (mainChart) mainChart.destroy();

    mainChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Botol",
                    data: botolData,
                    backgroundColor: "#52b788",
                    borderRadius: 8,
                    borderWidth: 0,
                },
                {
                    label: "Berat (kg)",
                    data: beratData,
                    backgroundColor: "#e9c46a",
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
                legend: { position: "top" },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: "#e9edf2" },
                    title: { display: true, text: "Jumlah", color: "#5f7c8b" },
                },
                x: {
                    grid: { display: false },
                    title: { display: true, text: "Periode", color: "#5f7c8b" },
                },
            },
        },
    });
}

/* =====================================================
   UNDUH LAPORAN (.docx)
   - Ganti metode html2canvas → endpoint /laporan/export-docx
   - Server generate file Word dari template + data DB
   ===================================================== */
function downloadReport() {
    const startDate = document.getElementById("startDate")?.value || "";
    const endDate = document.getElementById("endDate")?.value || "";

    const params = new URLSearchParams({
        period: currentPeriod,
        start_date: startDate,
        end_date: endDate,
    });

    showNotification("📄 Menyiapkan laporan Word...", "info");

    // Trigger download via server (browser akan download otomatis dari response)
    window.location.href = `/api/laporan/export-docx?${params}`;

    // Notifikasi sukses (delay dikit biar kelihatan progres)
    setTimeout(() => {
        showNotification("✅ File laporan sedang diunduh", "success");
    }, 800);
}

/* =====================================================
   NOTIFIKASI
   ===================================================== */
function showNotification(message, type = "success") {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `<i class="fas ${
        type === "success"
            ? "fa-check-circle"
            : type === "error"
              ? "fa-exclamation-circle"
              : "fa-info-circle"
    }"></i><span>${message}</span>`;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = "slideOutRight 0.3s ease";
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

/* =====================================================
   NAVIGASI
   ===================================================== */
const navToggle = document.getElementById("navToggle");
const navigation = document.getElementById("navigation");
const overlay = document.getElementById("overlay");

function toggleNav() {
    if (navToggle) navToggle.classList.toggle("active");
    if (navigation) navigation.classList.toggle("active");
    if (overlay) overlay.classList.toggle("active");
}

/* =====================================================
   EVENT LISTENERS
   ===================================================== */
function initEventListeners() {
    if (navToggle) navToggle.addEventListener("click", toggleNav);
    if (overlay) overlay.addEventListener("click", toggleNav);

    // Filter periode (weekly / monthly / yearly)
    const filterBtns = document.querySelectorAll(".filter-btn");
    filterBtns.forEach((btn) => {
        btn.addEventListener("click", () => {
            filterBtns.forEach((b) => b.classList.remove("active"));
            btn.classList.add("active");
            currentPeriod = btn.dataset.period;
            fetchChartData(currentPeriod);
        });
    });

    // Tombol unduh laporan
    const downloadBtn = document.getElementById("downloadReportBtn");
    if (downloadBtn) {
        downloadBtn.addEventListener("click", downloadReport);
    }
}

/* =====================================================
   LOGOUT
   ===================================================== */
const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
        window.location.href = "/logout";
    });
}

/* =====================================================
   INIT
   ===================================================== */
async function init() {
    await fetchChartData("weekly");
    initEventListeners();

    window.addEventListener("resize", () => {
        if (mainChart) mainChart.resize();
    });
}

document.addEventListener("DOMContentLoaded", init);
