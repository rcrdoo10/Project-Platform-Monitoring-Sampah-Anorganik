const penukaranPerHari = [
  12, 8, 15, 10, 7, 14, 18, 22, 19, 25, 30, 28, 35, 32, 40, 38, 45, 42, 50, 48,
  55, 52, 60, 58, 65, 62, 70, 68, 75, 72,
];

const labels = [];
for (let i = 1; i <= 30; i++) {
  labels.push(`${i} Mei`);
}

let allHistoryData = [
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
  {
    tanggal: "25 Mei 2026, 10:00",
    reward: "Snack Sehat",
    poin: 150,
    status: "Disetujui",
  },
  {
    tanggal: "24 Mei 2026, 13:45",
    reward: "Air Mineral",
    poin: 100,
    status: "Disetujui",
  },
  {
    tanggal: "23 Mei 2026, 15:30",
    reward: "Pupuk Organik",
    poin: 300,
    status: "Disetujui",
  },
  {
    tanggal: "22 Mei 2026, 14:30",
    reward: "Paket Nasi Box",
    poin: 500,
    status: "Disetujui",
  },
  {
    tanggal: "21 Mei 2026, 12:00",
    reward: "Jus Buah Segar",
    poin: 300,
    status: "Disetujui",
  },
];

let totalReward = 1250;
let pendingCount = 3;
let totalTransaksi = 2847;

let mainChart;

const navToggle = document.getElementById("navToggle");
const navigation = document.getElementById("navigation");
const overlay = document.getElementById("overlay");
const fullHistoryModal = document.getElementById("fullHistoryModal");
const lihatSemuaBtn = document.getElementById("lihatSemuaRiwayatBtn");
const fullHistoryClose = document.querySelector(".full-history-close");

function loadData() {
  const savedReward = localStorage.getItem("totalReward");
  const savedPending = localStorage.getItem("pendingCount");
  const savedTransaksi = localStorage.getItem("totalTransaksi");
  const savedHistory = localStorage.getItem("allHistoryData");

  if (savedReward) totalReward = parseInt(savedReward);
  if (savedPending) pendingCount = parseInt(savedPending);
  if (savedTransaksi) totalTransaksi = parseInt(savedTransaksi);
  if (savedHistory) allHistoryData = JSON.parse(savedHistory);

  updateDashboardNumbers();
}

function saveData() {
  localStorage.setItem("totalReward", totalReward);
  localStorage.setItem("pendingCount", pendingCount);
  localStorage.setItem("totalTransaksi", totalTransaksi);
  localStorage.setItem("allHistoryData", JSON.stringify(allHistoryData));
}

function renderHistoryTable() {
  const tbody = document.getElementById("historyBody");
  if (!tbody) return;

  const recentData = allHistoryData.slice(0, 5);
  tbody.innerHTML = "";
  recentData.forEach((item) => {
    const row = tbody.insertRow();
    row.insertCell(0).innerText = item.tanggal;
    row.insertCell(1).innerHTML =
      `<strong style="color:#2e7d64;">${item.reward}</strong>`;
    row.insertCell(2).innerHTML =
      `<span style="color:#e9c46a; font-weight:600;">${item.poin.toLocaleString()} poin</span>`;
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
  allHistoryData.forEach((item) => {
    const row = tbody.insertRow();
    row.insertCell(0).innerText = item.tanggal;
    row.insertCell(1).innerHTML =
      `<strong style="color:#2e7d64;">${item.reward}</strong>`;
    row.insertCell(2).innerHTML =
      `<span style="color:#e9c46a; font-weight:600;">${item.poin.toLocaleString()} poin</span>`;
    row.insertCell(3).innerHTML =
      `<span class="status-badge-approved"><i class="fas fa-check-circle"></i> ${item.status}</span>`;
    row.insertCell(4).innerHTML =
      `<a href="#" class="detail-link" data-tgl="${item.tanggal}">Lihat detail</a>`;
  });

  document.querySelectorAll("#fullHistoryBody .detail-link").forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const tgl = link.getAttribute("data-tgl") || "transaksi";
      showNotification(`✨ Detail penukaran reward: ${tgl}`, "info");
    });
  });
}

function openFullHistoryModal() {
  renderFullHistoryTable();
  fullHistoryModal.style.display = "block";
  document.body.style.overflow = "hidden";
}

function closeFullHistoryModal() {
  fullHistoryModal.style.display = "none";
  document.body.style.overflow = "";
}

function initMainChart() {
  const ctx = document.getElementById("wasteChart").getContext("2d");
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

function updateDashboardNumbers() {
  document.getElementById("totalRewardVal").innerText =
    totalReward.toLocaleString("id-ID");
  document.getElementById("pendingCount").innerText = pendingCount;
  document.getElementById("totalTransaksiVal").innerText =
    totalTransaksi.toLocaleString("id-ID");
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

function init() {
  loadData();
  renderHistoryTable();
  initMainChart();
  updateDashboardNumbers();
  initEventListeners();

  window.addEventListener("resize", () => {
    if (mainChart) mainChart.resize();
  });
}

document.addEventListener("DOMContentLoaded", init);