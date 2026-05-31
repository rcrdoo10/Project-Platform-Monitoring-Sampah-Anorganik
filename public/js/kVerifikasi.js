let pendingTransactions = [
  {
    id: 1,
    tanggal: "31 Mei 2026, 10:15",
    nama: "Budi Santoso",
    reward: "Paket Nasi Box",
    poin: 500,
    status: "pending",
  },
  {
    id: 2,
    tanggal: "31 Mei 2026, 09:30",
    nama: "Siti Aminah",
    reward: "Jus Buah Segar",
    poin: 300,
    status: "pending",
  },
  {
    id: 3,
    tanggal: "30 Mei 2026, 20:45",
    nama: "Agus Wijaya",
    reward: "Eco Bag",
    poin: 200,
    status: "pending",
  },
  {
    id: 4,
    tanggal: "30 Mei 2026, 15:20",
    nama: "Dewi Lestari",
    reward: "Kopi Spesial",
    poin: 250,
    status: "pending",
  },
  {
    id: 5,
    tanggal: "29 Mei 2026, 11:00",
    nama: "Rina Febrianti",
    reward: "Tumbler",
    poin: 800,
    status: "pending",
  },
];

let approvedTransactions = [
  {
    id: 6,
    tanggal: "28 Mei 2026, 14:30",
    nama: "Hendra Putra",
    reward: "Paket Nasi Box",
    poin: 500,
    status: "approved",
  },
  {
    id: 7,
    tanggal: "27 Mei 2026, 09:15",
    nama: "Linda Wati",
    reward: "Eco Bag",
    poin: 200,
    status: "approved",
  },
  {
    id: 8,
    tanggal: "26 Mei 2026, 16:45",
    nama: "Rudi Hartono",
    reward: "Jus Buah Segar",
    poin: 300,
    status: "approved",
  },
  {
    id: 9,
    tanggal: "25 Mei 2026, 10:30",
    nama: "Sarah Azzahra",
    reward: "Kopi Spesial",
    poin: 250,
    status: "approved",
  },
  {
    id: 10,
    tanggal: "24 Mei 2026, 13:20",
    nama: "Fajar Nugroho",
    reward: "Tumbler",
    poin: 800,
    status: "approved",
  },
];

let rewardsData = [
  {
    id: 1,
    name: "Paket Nasi Box",
    points: 500,
    stock: 25,
    category: "food",
    desc: "Paket nasi lengkap dengan lauk",
  },
  {
    id: 2,
    name: "Jus Buah Segar",
    points: 300,
    stock: 40,
    category: "drink",
    desc: "Jus buah segar tanpa pengawet",
  },
  {
    id: 3,
    name: "Kopi Spesial",
    points: 250,
    stock: 50,
    category: "drink",
    desc: "Kopi pilihan berkualitas",
  },
  {
    id: 4,
    name: "Eco Bag",
    points: 200,
    stock: 50,
    category: "goods",
    desc: "Tas belanja ramah lingkungan",
  },
  {
    id: 5,
    name: "Tumbler",
    points: 800,
    stock: 15,
    category: "goods",
    desc: "Tumbler stainless steel",
  },
];

let totalReward = rewardsData.reduce((sum, r) => sum + r.stock, 0);
let totalTransaksi = 2847;
let currentVerifikasiTab = "pending";

const navToggle = document.getElementById("navToggle");
const navigation = document.getElementById("navigation");
const overlay = document.getElementById("overlay");

function loadData() {
  const savedPending = localStorage.getItem("pendingTransactions");
  const savedApproved = localStorage.getItem("approvedTransactions");
  const savedRewards = localStorage.getItem("rewardsData");

  if (savedPending) pendingTransactions = JSON.parse(savedPending);
  if (savedApproved) approvedTransactions = JSON.parse(savedApproved);
  if (savedRewards) rewardsData = JSON.parse(savedRewards);

  totalReward = rewardsData.reduce((sum, r) => sum + r.stock, 0);
  updateDashboardData();
}

function saveData() {
  localStorage.setItem(
    "pendingTransactions",
    JSON.stringify(pendingTransactions),
  );
  localStorage.setItem(
    "approvedTransactions",
    JSON.stringify(approvedTransactions),
  );
  localStorage.setItem("rewardsData", JSON.stringify(rewardsData));
  localStorage.setItem("totalReward", totalReward);
}

function updateDashboardData() {
  localStorage.setItem("totalReward", totalReward);
  localStorage.setItem("pendingCount", pendingTransactions.length);
  localStorage.setItem("totalTransaksi", totalTransaksi);

  let allHistoryData = [];
  approvedTransactions.forEach((t) => {
    allHistoryData.unshift({
      tanggal: t.tanggal,
      reward: t.reward,
      poin: t.poin,
      status: "Disetujui",
    });
  });
  localStorage.setItem("allHistoryData", JSON.stringify(allHistoryData));
}

function renderVerifikasiTable() {
  const tbody = document.getElementById("verifikasiBody");
  const tableTitle = document.getElementById("tableTitle");

  if (!tbody) return;
  tbody.innerHTML = "";

  let data;
  if (currentVerifikasiTab === "pending") {
    data = pendingTransactions;
    tableTitle.innerText = "Transaksi Menunggu Verifikasi";
  } else {
    data = approvedTransactions;
    tableTitle.innerText = "Riwayat Transaksi Disetujui";
  }

  data.forEach((item) => {
    const row = tbody.insertRow();
    row.insertCell(0).innerText = item.tanggal;
    row.insertCell(1).innerHTML = `<strong>${item.nama}</strong>`;
    row.insertCell(2).innerHTML =
      `<span style="color:#2e7d64;">${item.reward}</span>`;
    row.insertCell(3).innerHTML =
      `<span style="color:#e9c46a; font-weight:600;">${item.poin.toLocaleString()} poin</span>`;

    if (currentVerifikasiTab === "pending") {
      row.insertCell(4).innerHTML =
        `<span class="status-badge-pending"><i class="fas fa-clock"></i> Pending</span>`;
      row.insertCell(5).innerHTML = `
                <button class="approve-btn" data-id="${item.id}"><i class="fas fa-check-circle"></i> Setujui</button>
                <a href="#" class="detail-link" data-tgl="${item.tanggal}">Detail</a>
            `;
    } else {
      row.insertCell(4).innerHTML =
        `<span class="status-badge-approved"><i class="fas fa-check-circle"></i> Disetujui</span>`;
      row.insertCell(5).innerHTML =
        `<a href="#" class="detail-link" data-tgl="${item.tanggal}">Lihat detail</a>`;
    }
  });

  if (currentVerifikasiTab === "pending") {
    document.querySelectorAll(".approve-btn").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        const id = parseInt(btn.dataset.id);
        approveTransaction(id);
      });
    });
  }

  document.querySelectorAll(".detail-link").forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const tgl = link.getAttribute("data-tgl") || "transaksi";
      showNotification(`✨ Detail transaksi: ${tgl}`, "info");
    });
  });
}

function approveTransaction(id) {
  const transaction = pendingTransactions.find((t) => t.id === id);
  if (transaction) {
    const index = pendingTransactions.findIndex((t) => t.id === id);
    pendingTransactions.splice(index, 1);

    const reward = rewardsData.find((r) => r.name === transaction.reward);
    if (reward && reward.stock > 0) {
      reward.stock -= 1;
      totalReward -= 1;
    }

    const approvedTrans = {
      ...transaction,
      status: "approved",
    };
    approvedTransactions.unshift(approvedTrans);

    totalTransaksi += 1;

    saveData();
    updateDashboardData();
    renderVerifikasiTable();

    showNotification(
      `✅ Transaksi dari ${transaction.nama} telah disetujui! Stok reward berkurang 1.`,
      "success",
    );
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

  const tabBtns = document.querySelectorAll(".tab-btn");
  tabBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      tabBtns.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      currentVerifikasiTab = btn.dataset.status;
      renderVerifikasiTable();
    });
  });
}

function init() {
  loadData();
  renderVerifikasiTable();
  initEventListeners();
}

document.addEventListener("DOMContentLoaded", init);