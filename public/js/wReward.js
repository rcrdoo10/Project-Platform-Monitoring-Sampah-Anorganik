let totalPoin = 9999;
let selectedReward = null;
let selectedQuantity = 1;
let currentCategoryFilter = "all";
let currentHistoryFilter = "all";

// Data reward
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
    name: "Voucher Makanan",
    points: 400,
    stock: 30,
    category: "food",
    desc: "Voucher untuk makan di restoran mitra",
  },
  {
    id: 4,
    name: "Kopi Spesial",
    points: 250,
    stock: 50,
    category: "drink",
    desc: "Kopi pilihan berkualitas",
  },
  {
    id: 5,
    name: "Tumbler Stainless Steel",
    points: 800,
    stock: 15,
    category: "goods",
    desc: "Tumbler ramah lingkungan",
  },
  {
    id: 6,
    name: "Eco Bag",
    points: 200,
    stock: 50,
    category: "goods",
    desc: "Tas belanja ramah lingkungan",
  },
  {
    id: 7,
    name: "Pupuk Organik 5kg",
    points: 300,
    stock: 40,
    category: "goods",
    desc: "Pupuk untuk tanaman",
  },
  {
    id: 8,
    name: "Donasi Penanaman Pohon",
    points: 1000,
    stock: 100,
    category: "goods",
    desc: "Donasi untuk menanam 1 pohon",
  },
  {
    id: 9,
    name: "Snack Sehat",
    points: 150,
    stock: 60,
    category: "food",
    desc: "Camilan sehat organik",
  },
  {
    id: 10,
    name: "Air Mineral",
    points: 100,
    stock: 80,
    category: "drink",
    desc: "Air mineral kemasan",
  },
];

// Riwayat penukaran user
let userExchangeHistory = [
  {
    id: 1,
    tanggal: "30 Mei 2026, 14:30",
    reward: "Paket Nasi Box",
    jumlah: 2,
    totalPoin: 1000,
    status: "pending",
  },
  {
    id: 2,
    tanggal: "28 Mei 2026, 10:15",
    reward: "Tumbler Stainless Steel",
    jumlah: 1,
    totalPoin: 800,
    status: "approved",
  },
  {
    id: 3,
    tanggal: "25 Mei 2026, 09:45",
    reward: "Kopi Spesial",
    jumlah: 3,
    totalPoin: 750,
    status: "approved",
  },
  {
    id: 4,
    tanggal: "27 Mei 2026, 16:20",
    reward: "Voucher Makanan",
    jumlah: 2,
    totalPoin: 800,
    status: "pending",
  },
  {
    id: 5,
    tanggal: "22 Mei 2026, 11:00",
    reward: "Eco Bag",
    jumlah: 1,
    totalPoin: 200,
    status: "approved",
  },
];

function loadData() {
  const savedPoin = localStorage.getItem("totalPoin");
  const savedRewards = localStorage.getItem("rewardsData");
  const savedHistory = localStorage.getItem("userExchangeHistory");

  if (savedPoin) totalPoin = parseInt(savedPoin);
  if (savedRewards) rewardsData = JSON.parse(savedRewards);
  if (savedHistory) userExchangeHistory = JSON.parse(savedHistory);

  updatePoinDisplay();
}

function saveData() {
  localStorage.setItem("totalPoin", totalPoin);
  localStorage.setItem("rewardsData", JSON.stringify(rewardsData));
  localStorage.setItem(
    "userExchangeHistory",
    JSON.stringify(userExchangeHistory),
  );
}

function updatePoinDisplay() {
  const rewardPoinDisplay = document.getElementById("rewardPoinDisplay");
  if (rewardPoinDisplay)
    rewardPoinDisplay.innerText = totalPoin.toLocaleString("id-ID");
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

function renderUserExchangeHistory() {
  const tbody = document.getElementById("exchangeHistoryBody");
  if (!tbody) return;

  let filteredData = userExchangeHistory;
  if (currentHistoryFilter !== "all") {
    filteredData = userExchangeHistory.filter(
      (item) => item.status === currentHistoryFilter,
    );
  }

  tbody.innerHTML = "";
  filteredData.forEach((item) => {
    const row = tbody.insertRow();
    row.insertCell(0).innerText = item.tanggal;
    row.insertCell(1).innerHTML =
      `<strong style="color:#2e7d64;">${item.reward}</strong>`;
    row.insertCell(2).innerHTML = `${item.jumlah} x`;
    row.insertCell(3).innerHTML =
      `<span style="color:#e9c46a; font-weight:600;">${item.totalPoin.toLocaleString()} poin</span>`;

    if (item.status === "pending") {
      row.insertCell(4).innerHTML =
        `<span class="status-badge-pending"><i class="fas fa-clock"></i> Menunggu Verifikasi</span>`;
      row.insertCell(5).innerHTML =
        `<a href="#" class="detail-link" data-id="${item.id}">Lihat detail</a>`;
    } else {
      row.insertCell(4).innerHTML =
        `<span class="status-badge-approved"><i class="fas fa-check-circle"></i> Disetujui</span>`;
      row.insertCell(5).innerHTML =
        `<a href="#" class="detail-link" data-id="${item.id}">Lihat detail</a>`;
    }
  });

  document
    .querySelectorAll("#exchangeHistoryBody .detail-link")
    .forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        const id = parseInt(link.getAttribute("data-id"));
        const transaction = userExchangeHistory.find((t) => t.id === id);
        if (transaction) {
          showNotification(
            `✨ Detail penukaran: ${transaction.jumlah}x ${transaction.reward} pada ${transaction.tanggal}. Total poin: ${transaction.totalPoin.toLocaleString()} poin. Status: ${transaction.status === "pending" ? "Menunggu verifikasi" : "Telah disetujui"}.`,
            "info",
          );
        }
      });
    });
}

function renderRewards() {
  const container = document.getElementById("rewardItemsContainer");
  if (!container) return;

  let filteredRewards = rewardsData;
  if (currentCategoryFilter !== "all") {
    filteredRewards = rewardsData.filter(
      (reward) => reward.category === currentCategoryFilter,
    );
  }

  container.innerHTML = "";
  filteredRewards.forEach((reward) => {
    const isLowStock = reward.stock <= 5;
    const stockDisplay = reward.stock > 0 ? `${reward.stock} tersisa` : "Habis";
    const isOutOfStock = reward.stock <= 0;

    let categoryClass = "";
    let iconName = "";

    if (reward.category === "food") {
      categoryClass = "food";
      iconName = "fa-utensils";
    } else if (reward.category === "drink") {
      categoryClass = "drink";
      iconName = "fa-mug-hot";
    } else {
      categoryClass = "goods";
      iconName = "fa-box";
    }

    const rewardDiv = document.createElement("div");
    rewardDiv.className = "reward-item";
    rewardDiv.setAttribute("data-id", reward.id);

    rewardDiv.innerHTML = `
            <div class="reward-info">
                <div class="reward-icon ${categoryClass}">
                    <i class="fas ${iconName}"></i>
                </div>
                <div class="reward-details">
                    <h4>${reward.name}</h4>
                    <p>${reward.desc}</p>
                </div>
            </div>
            <div class="reward-stock ${isLowStock && reward.stock > 0 ? "low-stock" : ""}">
                <i class="fas fa-boxes"></i>
                <span>Stok: ${stockDisplay}</span>
            </div>
            <div class="reward-price">
                <i class="fas fa-star"></i> ${reward.points.toLocaleString()} poin/unit
            </div>
            <div class="exchange-control">
                <div class="quantity-selector">
                    <button class="qty-minus" data-id="${reward.id}" ${isOutOfStock ? "disabled" : ""}>-</button>
                    <span id="qty-${reward.id}">1</span>
                    <button class="qty-plus" data-id="${reward.id}" ${isOutOfStock ? "disabled" : ""}>+</button>
                </div>
                <div class="total-points">
                    Total: <strong id="total-${reward.id}">${reward.points.toLocaleString()}</strong> poin
                </div>
                <button class="exchange-btn" data-id="${reward.id}" ${isOutOfStock ? "disabled" : ""}>
                    <i class="fas fa-exchange-alt"></i> Tukar
                </button>
            </div>
        `;
    container.appendChild(rewardDiv);
  });

  // Event listener quantity
  filteredRewards.forEach((reward) => {
    const minusBtn = document.querySelector(
      `.qty-minus[data-id="${reward.id}"]`,
    );
    const plusBtn = document.querySelector(`.qty-plus[data-id="${reward.id}"]`);
    const qtySpan = document.getElementById(`qty-${reward.id}`);
    const totalSpan = document.getElementById(`total-${reward.id}`);

    if (minusBtn) {
      minusBtn.addEventListener("click", () => {
        let currentQty = parseInt(qtySpan.innerText);
        if (currentQty > 1) {
          currentQty--;
          qtySpan.innerText = currentQty;
          totalSpan.innerText = (reward.points * currentQty).toLocaleString();
        }
      });
    }

    if (plusBtn) {
      plusBtn.addEventListener("click", () => {
        let currentQty = parseInt(qtySpan.innerText);
        if (currentQty < reward.stock) {
          currentQty++;
          qtySpan.innerText = currentQty;
          totalSpan.innerText = (reward.points * currentQty).toLocaleString();
        } else {
          showNotification(
            `⚠️ Stok tidak mencukupi! Maksimal ${reward.stock} item.`,
            "error",
          );
        }
      });
    }
  });

  // Event listener tukar
  document.querySelectorAll(".exchange-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const id = parseInt(btn.dataset.id);
      const reward = rewardsData.find((r) => r.id === id);
      const qtySpan = document.getElementById(`qty-${reward.id}`);
      const quantity = parseInt(qtySpan.innerText);

      if (reward && reward.stock >= quantity) {
        const totalPointsNeeded = reward.points * quantity;

        if (totalPoin >= totalPointsNeeded) {
          selectedReward = reward;
          selectedQuantity = quantity;

          document.getElementById("confirmMessage").innerHTML = `
                        Anda akan menukarkan <strong>${quantity}x ${reward.name}</strong><br>
                        Total poin yang dibutuhkan: <strong style="color:#e9c46a;">${totalPointsNeeded.toLocaleString()} poin</strong><br>
                        Stok setelah ditukar: <strong>${reward.stock - quantity}</strong>
                    `;
          document.getElementById("confirmModal").style.display = "block";
          document.body.style.overflow = "hidden";
        } else {
          showNotification(
            `❌ Poin tidak mencukupi! Butuh ${totalPointsNeeded.toLocaleString()} poin, Anda memiliki ${totalPoin.toLocaleString()} poin.`,
            "error",
          );
        }
      } else {
        showNotification(
          `❌ Stok tidak mencukupi! Tersedia ${reward.stock} item.`,
          "error",
        );
      }
    });
  });
}

function processExchange() {
  if (selectedReward && selectedQuantity) {
    const totalPointsNeeded = selectedReward.points * selectedQuantity;

    totalPoin -= totalPointsNeeded;

    const rewardIndex = rewardsData.findIndex(
      (r) => r.id === selectedReward.id,
    );
    if (rewardIndex !== -1) {
      rewardsData[rewardIndex].stock -= selectedQuantity;
    }

    const now = new Date();
    const tanggalString =
      now.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
      }) +
      ", " +
      now.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });

    const newId = userExchangeHistory.length + 1;
    userExchangeHistory.unshift({
      id: newId,
      tanggal: tanggalString,
      reward: selectedReward.name,
      jumlah: selectedQuantity,
      totalPoin: totalPointsNeeded,
      status: "pending",
    });

    saveData();
    updatePoinDisplay();
    renderRewards();
    renderUserExchangeHistory();

    showNotification(
      `🎉 Permintaan penukaran ${selectedQuantity}x ${selectedReward.name} berhasil! Menunggu verifikasi admin.`,
      "success",
    );

    closeConfirmModal();

    selectedReward = null;
    selectedQuantity = 1;
  }
}

function closeConfirmModal() {
  document.getElementById("confirmModal").style.display = "none";
  document.body.style.overflow = "";
}

// Navigasi
const navToggle = document.getElementById("navToggle");
const navigation = document.getElementById("navigation");
const overlay = document.getElementById("overlay");

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

  // Filter kategori
  const categoryBtns = document.querySelectorAll(".category-btn");
  categoryBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      categoryBtns.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      currentCategoryFilter = btn.dataset.category;
      renderRewards();
    });
  });

  // Filter status
  const filterBtns = document.querySelectorAll(".status-filter-btn");
  filterBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      filterBtns.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      currentHistoryFilter = btn.dataset.filter;
      renderUserExchangeHistory();
    });
  });

  // Modal events
  const modalClose = document.querySelector(".modal-close");
  const cancelConfirmBtn = document.getElementById("cancelConfirmBtn");
  const confirmExchangeBtn = document.getElementById("confirmExchangeBtn");

  if (modalClose) modalClose.addEventListener("click", closeConfirmModal);
  if (cancelConfirmBtn)
    cancelConfirmBtn.addEventListener("click", closeConfirmModal);
  if (confirmExchangeBtn)
    confirmExchangeBtn.addEventListener("click", processExchange);

  window.addEventListener("click", (e) => {
    const modal = document.getElementById("confirmModal");
    if (e.target === modal) closeConfirmModal();
  });
}

function init() {
  loadData();
  renderRewards();
  renderUserExchangeHistory();
  initEventListeners();
}

document.addEventListener("DOMContentLoaded", init);