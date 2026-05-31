// Data Dashboard
let labels = ["1 Mei", "8 Mei", "15 Mei", "22 Mei", "29 Mei"];
let poinData = [120, 145, 168, 192, 220];
let botolData = [100, 118, 134, 155, 170];
let beratData = [78, 92, 105, 122, 130];

// Semua data riwayat
let allHistoryData = [
  {
    tanggal: "1 Mei 2026, 10:30",
    botol: "100 Botol",
    berat: "1,5 kg",
    poin: "+150",
    status: "Berhasil",
  },
  {
    tanggal: "3 Mei 2026, 14:15",
    botol: "45 Botol",
    berat: "0,7 kg",
    poin: "+67",
    status: "Berhasil",
  },
  {
    tanggal: "8 Mei 2026, 09:45",
    botol: "120 Botol",
    berat: "1,8 kg",
    poin: "+180",
    status: "Berhasil",
  },
  {
    tanggal: "15 Mei 2026, 11:20",
    botol: "80 Botol",
    berat: "1,2 kg",
    poin: "+120",
    status: "Berhasil",
  },
  {
    tanggal: "22 Mei 2026, 16:00",
    botol: "150 Botol",
    berat: "2,3 kg",
    poin: "+225",
    status: "Berhasil",
  },
  {
    tanggal: "25 Mei 2026, 09:00",
    botol: "30 Botol",
    berat: "0,5 kg",
    poin: "+45",
    status: "Berhasil",
  },
  {
    tanggal: "27 Mei 2026, 13:30",
    botol: "60 Botol",
    berat: "0,9 kg",
    poin: "+90",
    status: "Berhasil",
  },
  {
    tanggal: "29 Mei 2026, 11:45",
    botol: "40 Botol",
    berat: "0,6 kg",
    poin: "+60",
    status: "Berhasil",
  },
];

let totalPoin = 9999;
let totalBotol = 9999;
let totalBerat = 9999;

let mainChart;

// Element references
const navToggle = document.getElementById("navToggle");
const navigation = document.getElementById("navigation");
const overlay = document.getElementById("overlay");
const fullHistoryModal = document.getElementById("fullHistoryModal");
const lihatSemuaBtn = document.getElementById("lihatSemuaRiwayatBtn");
const fullHistoryClose = document.querySelector(".full-history-close");

// Load data dari localStorage
function loadData() {
  const savedPoin = localStorage.getItem("totalPoin");
  const savedBotol = localStorage.getItem("totalBotol");
  const savedBerat = localStorage.getItem("totalBerat");
  const savedHistory = localStorage.getItem("allHistoryData");

  if (savedPoin) totalPoin = parseInt(savedPoin);
  if (savedBotol) totalBotol = parseInt(savedBotol);
  if (savedBerat) totalBerat = parseFloat(savedBerat);
  if (savedHistory) allHistoryData = JSON.parse(savedHistory);
}

function saveData() {
  localStorage.setItem("totalPoin", totalPoin);
  localStorage.setItem("totalBotol", totalBotol);
  localStorage.setItem("totalBerat", totalBerat);
  localStorage.setItem("allHistoryData", JSON.stringify(allHistoryData));
}

// Render tabel riwayat (5 terbaru)
function renderHistoryTable() {
  const tbody = document.getElementById("historyBody");
  if (!tbody) return;

  const recentData = allHistoryData.slice(0, 5);
  tbody.innerHTML = "";
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
      `<a href="#" class="detail-link" data-tgl="${item.tanggal}">Lihat detail</a>`;
  });

  document.querySelectorAll("#historyBody .detail-link").forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const tgl = link.getAttribute("data-tgl") || "transaksi";
      showNotification(`✨ Detail transaksi: ${tgl}`, "info");
    });
  });
}

// Render semua riwayat untuk modal
function renderFullHistoryTable() {
  const tbody = document.getElementById("fullHistoryBody");
  if (!tbody) return;

  tbody.innerHTML = "";
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
      `<a href="#" class="detail-link" data-tgl="${item.tanggal}">Lihat detail</a>`;
  });

  document.querySelectorAll("#fullHistoryBody .detail-link").forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const tgl = link.getAttribute("data-tgl") || "transaksi";
      showNotification(`✨ Detail transaksi: ${tgl}`, "info");
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
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Poin",
          data: poinData,
          borderColor: "#2e7d64",
          borderWidth: 3,
          tension: 0.2,
          fill: false,
          pointBackgroundColor: "#2e7d64",
          pointRadius: 5,
        },
        {
          label: "Botol",
          data: botolData,
          borderColor: "#52b788",
          borderWidth: 3,
          tension: 0.2,
          fill: false,
          pointBackgroundColor: "#52b788",
          pointRadius: 5,
        },
        {
          label: "Berat (kg)",
          data: beratData,
          borderColor: "#e9c46a",
          borderWidth: 3,
          tension: 0.2,
          fill: false,
          pointBackgroundColor: "#e9c46a",
          pointRadius: 5,
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
        y: { beginAtZero: true, grid: { color: "#e9edf2" } },
        x: { grid: { display: false } },
      },
    },
  });
}

function updateDashboardNumbers() {
  document.getElementById("totalPoinVal").innerText =
    totalPoin.toLocaleString("id-ID");
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