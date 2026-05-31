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
  {
    id: 6,
    name: "Snack Sehat",
    points: 150,
    stock: 60,
    category: "food",
    desc: "Camilan sehat organik",
  },
  {
    id: 7,
    name: "Air Mineral",
    points: 100,
    stock: 80,
    category: "drink",
    desc: "Air mineral kemasan",
  },
  {
    id: 8,
    name: "Pupuk Organik",
    points: 300,
    stock: 40,
    category: "goods",
    desc: "Pupuk untuk tanaman",
  },
];

let currentCategoryFilter = "all";
let nextRewardId = 9;

const navToggle = document.getElementById("navToggle");
const navigation = document.getElementById("navigation");
const overlay = document.getElementById("overlay");
const editModal = document.getElementById("editRewardModal");
const addModal = document.getElementById("addRewardModal");
const modalClose = document.querySelector(".modal-close");
const modalCloseAdd = document.querySelector(".modal-close-add");
const cancelEditBtn = document.getElementById("cancelEditBtn");
const cancelAddBtn = document.getElementById("cancelAddBtn");
const addRewardBtn = document.getElementById("addRewardBtn");

function loadData() {
  const savedRewards = localStorage.getItem("rewardsData");
  if (savedRewards) rewardsData = JSON.parse(savedRewards);
  renderRewards();
}

function saveData() {
  localStorage.setItem("rewardsData", JSON.stringify(rewardsData));
  localStorage.setItem(
    "totalReward",
    rewardsData.reduce((sum, r) => sum + r.stock, 0),
  );
}

function openEditRewardModal(rewardId) {
  const reward = rewardsData.find((r) => r.id === rewardId);
  if (reward) {
    document.getElementById("editRewardId").value = reward.id;
    document.getElementById("editRewardName").value = reward.name;
    document.getElementById("editRewardDesc").value = reward.desc;
    document.getElementById("editRewardCategory").value = reward.category;
    document.getElementById("editRewardPoints").value = reward.points;
    document.getElementById("editRewardStock").value = reward.stock;
    editModal.style.display = "block";
    document.body.style.overflow = "hidden";
  }
}

function saveRewardChanges() {
  const newName = document.getElementById("editRewardName").value;
  const newDesc = document.getElementById("editRewardDesc").value;
  const newCategory = document.getElementById("editRewardCategory").value;
  const newPoints = parseInt(document.getElementById("editRewardPoints").value);
  const newStock = parseInt(document.getElementById("editRewardStock").value);

  if (!newName.trim()) {
    showNotification("❌ Nama reward tidak boleh kosong!", "error");
    return;
  }

  if (isNaN(newPoints) || newPoints < 0) {
    showNotification("❌ Harga poin tidak valid!", "error");
    return;
  }

  if (isNaN(newStock) || newStock < 0) {
    showNotification("❌ Jumlah stok tidak valid!", "error");
    return;
  }

  const rewardId = parseInt(document.getElementById("editRewardId").value);
  const reward = rewardsData.find((r) => r.id === rewardId);
  if (reward) {
    reward.name = newName;
    reward.desc = newDesc;
    reward.category = newCategory;
    reward.points = newPoints;
    reward.stock = newStock;

    saveData();
    renderRewards();

    showNotification(
      `✅ Reward "${reward.name}" berhasil diupdate!`,
      "success",
    );
    closeEditModal();
  }
}

function closeEditModal() {
  editModal.style.display = "none";
  document.body.style.overflow = "";
}

function openAddRewardModal() {
  document.getElementById("addRewardName").value = "";
  document.getElementById("addRewardDesc").value = "";
  document.getElementById("addRewardCategory").value = "food";
  document.getElementById("addRewardPoints").value = 500;
  document.getElementById("addRewardStock").value = 10;
  addModal.style.display = "block";
  document.body.style.overflow = "hidden";
}

function closeAddModal() {
  addModal.style.display = "none";
  document.body.style.overflow = "";
}

function addNewReward() {
  const name = document.getElementById("addRewardName").value;
  const desc = document.getElementById("addRewardDesc").value;
  const category = document.getElementById("addRewardCategory").value;
  const points = parseInt(document.getElementById("addRewardPoints").value);
  const stock = parseInt(document.getElementById("addRewardStock").value);

  if (!name.trim()) {
    showNotification("❌ Nama reward tidak boleh kosong!", "error");
    return;
  }

  if (isNaN(points) || points < 0) {
    showNotification("❌ Harga poin tidak valid!", "error");
    return;
  }

  if (isNaN(stock) || stock < 0) {
    showNotification("❌ Jumlah stok tidak valid!", "error");
    return;
  }

  const newReward = {
    id: nextRewardId++,
    name: name,
    desc: desc || "Reward menarik untuk Anda",
    category: category,
    points: points,
    stock: stock,
  };

  rewardsData.push(newReward);
  saveData();
  renderRewards();

  showNotification(`✅ Reward "${name}" berhasil ditambahkan!`, "success");
  closeAddModal();
}

function deleteReward(rewardId) {
  const reward = rewardsData.find((r) => r.id === rewardId);
  if (
    reward &&
    confirm(`Apakah Anda yakin ingin menghapus reward "${reward.name}"?`)
  ) {
    const index = rewardsData.findIndex((r) => r.id === rewardId);
    rewardsData.splice(index, 1);
    saveData();
    renderRewards();
    showNotification(`🗑️ Reward "${reward.name}" berhasil dihapus!`, "info");
  }
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
    const isLowStock = reward.stock <= 10;
    const stockDisplay = reward.stock > 0 ? `${reward.stock} tersisa` : "Habis";

    let categoryIcon = "";
    let categoryClass = "";
    if (reward.category === "food") {
      categoryIcon = "fa-utensils";
      categoryClass = "food";
    } else if (reward.category === "drink") {
      categoryIcon = "fa-mug-hot";
      categoryClass = "drink";
    } else {
      categoryIcon = "fa-box";
      categoryClass = "goods";
    }

    const rewardDiv = document.createElement("div");
    rewardDiv.className = "reward-item";
    rewardDiv.innerHTML = `
            <div class="reward-info">
                <div class="reward-icon ${categoryClass}">
                    <i class="fas ${categoryIcon}"></i>
                </div>
                <div class="reward-details">
                    <h4>${reward.name}</h4>
                    <p>${reward.desc}</p>
                </div>
            </div>
            <div class="reward-stats">
                <div class="reward-points">
                    <i class="fas fa-star"></i> ${reward.points.toLocaleString()} poin
                </div>
                <div class="reward-stock ${isLowStock && reward.stock > 0 ? "low-stock" : ""}">
                    <i class="fas fa-boxes"></i> Stok: ${stockDisplay}
                </div>
            </div>
            <div class="reward-actions">
                <button class="edit-reward-btn" data-id="${reward.id}">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="delete-reward-btn" data-id="${reward.id}">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        `;
    container.appendChild(rewardDiv);
  });

  document.querySelectorAll(".edit-reward-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const id = parseInt(btn.dataset.id);
      openEditRewardModal(id);
    });
  });

  document.querySelectorAll(".delete-reward-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const id = parseInt(btn.dataset.id);
      deleteReward(id);
    });
  });
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

  const categoryBtns = document.querySelectorAll(".category-btn");
  categoryBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      categoryBtns.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      currentCategoryFilter = btn.dataset.category;
      renderRewards();
    });
  });

  if (modalClose) {
    modalClose.addEventListener("click", closeEditModal);
  }
  if (cancelEditBtn) {
    cancelEditBtn.addEventListener("click", closeEditModal);
  }

  if (modalCloseAdd) {
    modalCloseAdd.addEventListener("click", closeAddModal);
  }
  if (cancelAddBtn) {
    cancelAddBtn.addEventListener("click", closeAddModal);
  }

  if (addRewardBtn) {
    addRewardBtn.addEventListener("click", openAddRewardModal);
  }

  const editForm = document.getElementById("editRewardForm");
  if (editForm) {
    editForm.addEventListener("submit", (e) => {
      e.preventDefault();
      saveRewardChanges();
    });
  }

  const addForm = document.getElementById("addRewardForm");
  if (addForm) {
    addForm.addEventListener("submit", (e) => {
      e.preventDefault();
      addNewReward();
    });
  }

  window.addEventListener("click", (e) => {
    if (e.target === editModal) {
      closeEditModal();
    }
    if (e.target === addModal) {
      closeAddModal();
    }
  });
}

function init() {
  loadData();
  initEventListeners();
}

document.addEventListener("DOMContentLoaded", init);