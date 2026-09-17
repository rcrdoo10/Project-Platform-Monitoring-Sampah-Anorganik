let currentPeriod = "weekly";
let mainChart = null;

console.log("Data from server:", window.initialData);

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

async function downloadReport() {
    const reportSection = document.getElementById("reportSection");
    if (!reportSection) return;

    const originalBoxShadow = reportSection.style.boxShadow;
    const originalBorder = reportSection.style.border;

    reportSection.style.boxShadow = "none";
    reportSection.style.border = "1px solid #e9edf2";

    showNotification("📄 Sedang menyiapkan laporan...", "info");

    try {
        const canvas = await html2canvas(reportSection, {
            scale: 2,
            backgroundColor: "#ffffff",
            logging: false,
            useCORS: true,
        });

        const link = document.createElement("a");
        const period =
            currentPeriod === "weekly"
                ? "Mingguan"
                : currentPeriod === "monthly"
                  ? "Bulanan"
                  : "Tahunan";
        const date = new Date().toISOString().slice(0, 19).replace(/:/g, "-");
        link.download = `Laporan_Sampah_${period}_${date}.png`;
        link.href = canvas.toDataURL();
        link.click();

        showNotification("✅ Laporan berhasil diunduh!", "success");
    } catch (error) {
        console.error("Error:", error);
        showNotification("❌ Gagal mengunduh laporan", "error");
    } finally {
        reportSection.style.boxShadow = originalBoxShadow;
        reportSection.style.border = originalBorder;
    }
}

function showNotification(message, type = "success") {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `<i class="fas ${type === "success" ? "fa-check-circle" : type === "error" ? "fa-exclamation-circle" : "fa-info-circle"}"></i><span>${message}</span>`;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = "slideOutRight 0.3s ease";
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

const navToggle = document.getElementById("navToggle");
const navigation = document.getElementById("navigation");
const overlay = document.getElementById("overlay");

function toggleNav() {
    if (navToggle) navToggle.classList.toggle("active");
    if (navigation) navigation.classList.toggle("active");
    if (overlay) overlay.classList.toggle("active");
}

function initEventListeners() {
    if (navToggle) navToggle.addEventListener("click", toggleNav);
    if (overlay) overlay.addEventListener("click", toggleNav);

    const filterBtns = document.querySelectorAll(".filter-btn");
    filterBtns.forEach((btn) => {
        btn.addEventListener("click", () => {
            filterBtns.forEach((b) => b.classList.remove("active"));
            btn.classList.add("active");
            currentPeriod = btn.dataset.period;
            fetchChartData(currentPeriod);
        });
    });

    const downloadBtn = document.getElementById("downloadReportBtn");
    if (downloadBtn) {
        downloadBtn.addEventListener("click", downloadReport);
    }
}

const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
        window.location.href = "/logout";
    });
}

async function init() {
    await fetchChartData("weekly");
    initEventListeners();

    window.addEventListener("resize", () => {
        if (mainChart) mainChart.resize();
    });
}

document.addEventListener("DOMContentLoaded", init);
