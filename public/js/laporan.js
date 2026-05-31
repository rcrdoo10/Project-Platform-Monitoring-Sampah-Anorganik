const dailyData = {
  labels: [
    "1 Mei",
    "2 Mei",
    "3 Mei",
    "4 Mei",
    "5 Mei",
    "6 Mei",
    "7 Mei",
    "8 Mei",
    "9 Mei",
    "10 Mei",
    "11 Mei",
    "12 Mei",
    "13 Mei",
    "14 Mei",
    "15 Mei",
    "16 Mei",
    "17 Mei",
    "18 Mei",
    "19 Mei",
    "20 Mei",
    "21 Mei",
    "22 Mei",
    "23 Mei",
    "24 Mei",
    "25 Mei",
    "26 Mei",
    "27 Mei",
    "28 Mei",
    "29 Mei",
    "30 Mei",
  ],
  botol: [
    10, 12, 14, 15, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44,
    46, 48, 50, 52, 54, 56, 58, 60, 62, 64, 66,
  ],
  berat: [
    8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26,
    27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37,
  ],
};

let totalBotol = 9999;
let totalBerat = 9999;
let currentPeriod = "weekly";

let mainChart;

function getWeeklyData() {
  const weeks = [];
  const weeklyBotol = [];
  const weeklyBerat = [];

  for (let i = 0; i < 4; i++) {
    const start = i * 7;
    const end = Math.min(start + 7, dailyData.labels.length);
    const weekLabels = dailyData.labels.slice(start, end);
    const weekBotol = dailyData.botol
      .slice(start, end)
      .reduce((a, b) => a + b, 0);
    const weekBerat = dailyData.berat
      .slice(start, end)
      .reduce((a, b) => a + b, 0);

    weeks.push(`Minggu ${i + 1}`);
    weeklyBotol.push(weekBotol);
    weeklyBerat.push(weekBerat);
  }

  return {
    labels: weeks,
    botol: weeklyBotol,
    berat: weeklyBerat,
    labelText: "Mingguan (1-7 Mei, 8-14 Mei, 15-21 Mei, 22-28 Mei 2026)",
  };
}

function getMonthlyData() {
  const months = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "Mei",
    "Jun",
    "Jul",
    "Agu",
    "Sep",
    "Okt",
    "Nov",
    "Des",
  ];
  const monthlyBotol = new Array(12).fill(0);
  const monthlyBerat = new Array(12).fill(0);

  monthlyBotol[4] = dailyData.botol.reduce((a, b) => a + b, 0);
  monthlyBerat[4] = dailyData.berat.reduce((a, b) => a + b, 0);

  monthlyBotol[3] = 650;
  monthlyBerat[3] = 520;
  monthlyBotol[5] = 850;
  monthlyBerat[5] = 680;
  monthlyBotol[2] = 550;
  monthlyBerat[2] = 440;
  monthlyBotol[1] = 520;
  monthlyBerat[1] = 410;
  monthlyBotol[0] = 450;
  monthlyBerat[0] = 360;

  return {
    labels: months,
    botol: monthlyBotol,
    berat: monthlyBerat,
    labelText: "Bulanan - Januari s/d Desember 2026",
  };
}

function getYearlyData() {
  const years = ["2022", "2023", "2024", "2025", "2026"];
  const yearlyBotol = [1800, 2800, 4000, 6000, 8200];
  const yearlyBerat = [1200, 2000, 3000, 4800, 6500];

  return {
    labels: years,
    botol: yearlyBotol,
    berat: yearlyBerat,
    labelText: "Tahunan - 2022 s/d 2026",
  };
}

function updateChart(period) {
  let data;

  switch (period) {
    case "weekly":
      data = getWeeklyData();
      break;
    case "monthly":
      data = getMonthlyData();
      break;
    case "yearly":
      data = getYearlyData();
      break;
    default:
      data = getWeeklyData();
  }

  document.getElementById("periodLabel").innerText =
    `Periode: ${data.labelText}`;

  if (mainChart) {
    mainChart.data.labels = data.labels;
    mainChart.data.datasets[0].data = data.botol;
    mainChart.data.datasets[1].data = data.berat;
    mainChart.update();
  }
}

async function downloadReport() {
  const reportSection = document.getElementById("reportSection");
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

const navToggle = document.getElementById("navToggle");
const navigation = document.getElementById("navigation");
const overlay = document.getElementById("overlay");

function updateDashboardNumbers() {
  document.getElementById("totalBotolVal").innerText =
    totalBotol.toLocaleString("id-ID");
  document.getElementById("totalBeratVal").innerText =
    totalBerat.toLocaleString("id-ID");
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

function toggleNav() {
  navToggle.classList.toggle("active");
  navigation.classList.toggle("active");
  overlay.classList.toggle("active");
}

function initMainChart() {
  const ctx = document.getElementById("wasteChart").getContext("2d");
  if (mainChart) mainChart.destroy();

  const weeklyData = getWeeklyData();

  mainChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: weeklyData.labels,
      datasets: [
        {
          label: "Botol",
          data: weeklyData.botol,
          backgroundColor: "#52b788",
          borderRadius: 8,
          borderWidth: 0,
        },
        {
          label: "Berat (kg)",
          data: weeklyData.berat,
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
        tooltip: { mode: "index", intersect: false },
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

function initEventListeners() {
  navToggle.addEventListener("click", toggleNav);
  overlay.addEventListener("click", toggleNav);

  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      showNotification(
        "🚪 Anda telah logout dari Dompet Sadhar. Sampai jumpa kembali! 🌱",
        "info",
      );
    });
  }

  const filterBtns = document.querySelectorAll(".filter-btn");
  filterBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      filterBtns.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      currentPeriod = btn.dataset.period;
      updateChart(currentPeriod);
    });
  });

  const downloadBtn = document.getElementById("downloadReportBtn");
  if (downloadBtn) {
    downloadBtn.addEventListener("click", downloadReport);
  }
}

function init() {
  initMainChart();
  updateDashboardNumbers();
  initEventListeners();

  window.addEventListener("resize", () => {
    if (mainChart) mainChart.resize();
  });
}

document.addEventListener("DOMContentLoaded", init);