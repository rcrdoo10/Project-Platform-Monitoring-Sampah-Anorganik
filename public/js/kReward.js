// ========== VARIABEL GLOBAL ==========
let rewardsData = [];
let currentCategoryFilter = "all";

// ========== AMBIL DATA DARI DATABASE ==========
async function loadDataFromServer() {
    try {
        const response = await fetch("/api/all-rewards");
        const data = await response.json();

        if (data.success) {
            rewardsData = data.data;
            renderRewards();
        }
    } catch (error) {
        console.error("Error loading rewards:", error);
        showNotification("Gagal memuat data reward!", "error");
    }
}

// ========== SIMPAN REWARD KE DATABASE ==========
async function saveRewardToServer(rewardData, isUpdate = false, id = null) {
    try {
        let url = "/api/rewards";
        let method = "POST";

        if (isUpdate && id) {
            url = `/api/rewards/${id}`;
            method = "PUT";
        }

        const response = await fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrfToken(),
            },
            body: JSON.stringify(rewardData),
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message, "success");
            loadDataFromServer(); // Reload data
            return true;
        } else {
            showNotification(data.message || "Gagal menyimpan data", "error");
            return false;
        }
    } catch (error) {
        console.error("Error saving reward:", error);
        showNotification("Terjadi kesalahan!", "error");
        return false;
    }
}

// ========== HAPUS REWARD ==========
async function deleteRewardFromServer(id) {
    try {
        const response = await fetch(`/api/rewards/${id}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
            },
        });

        const data = await response.json();

        if (data.success) {
            showNotification(data.message, "success");
            loadDataFromServer();
            return true;
        } else {
            showNotification(data.message || "Gagal menghapus data", "error");
            return false;
        }
    } catch (error) {
        console.error("Error deleting reward:", error);
        showNotification("Terjadi kesalahan!", "error");
        return false;
    }
}

// ========== GET CSRF TOKEN ==========
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        return token.getAttribute("content");
    }
    return "";
}

// ========== RENDER REWARDS ==========
function renderRewards() {
    const container = document.getElementById("rewardItemsContainer");
    if (!container) return;

    let filteredRewards = rewardsData;
    if (currentCategoryFilter !== "all") {
        filteredRewards = rewardsData.filter(
            (reward) => reward.kategori === currentCategoryFilter,
        );
    }

    container.innerHTML = "";

    if (filteredRewards.length === 0) {
        container.innerHTML =
            '<div class="no-data">Belum ada data reward</div>';
        return;
    }

    filteredRewards.forEach((reward) => {
        const isLowStock = reward.stok <= 10;
        const stockDisplay =
            reward.stok > 0 ? `${reward.stok} tersisa` : "Habis";

        let categoryIcon = "";
        let categoryClass = "";
        if (
            reward.kategori === "Makanan" ||
            reward.jenis_reward === "Makanan"
        ) {
            categoryIcon = "fa-utensils";
            categoryClass = "food";
        } else if (
            reward.kategori === "Minuman" ||
            reward.jenis_reward === "Minuman"
        ) {
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
                    <h4>${escapeHtml(reward.nama_reward)}</h4>
                    <p>${escapeHtml(reward.deskripsi || "Reward menarik untuk Anda")}</p>
                </div>
            </div>
            <div class="reward-stats">
                <div class="reward-points">
                    <i class="fas fa-star"></i> ${Number(reward.poin_dibutuhkan).toLocaleString()} poin
                </div>
                <div class="reward-stock ${isLowStock && reward.stok > 0 ? "low-stock" : ""}">
                    <i class="fas fa-boxes"></i> Stok: ${stockDisplay}
                </div>
            </div>
            <div class="reward-actions">
                <button class="edit-reward-btn" data-id="${reward.id_reward}">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="delete-reward-btn" data-id="${reward.id_reward}">
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

// ========== ESCAPE HTML ==========
function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

// ========== EDIT REWARD ==========
function openEditRewardModal(rewardId) {
    const reward = rewardsData.find((r) => r.id_reward === rewardId);
    if (reward) {
        document.getElementById("editRewardId").value = reward.id_reward;
        document.getElementById("editRewardName").value = reward.nama_reward;
        document.getElementById("editRewardDesc").value =
            reward.deskripsi || "";
        document.getElementById("editRewardCategory").value =
            reward.kategori || reward.jenis_reward || "Makanan";
        document.getElementById("editRewardPoints").value =
            reward.poin_dibutuhkan;
        document.getElementById("editRewardStock").value = reward.stok;
        document.getElementById("editRewardModal").style.display = "block";
        document.body.style.overflow = "hidden";
    }
}

async function saveRewardChanges() {
    const id = document.getElementById("editRewardId").value;
    const rewardData = {
        nama_reward: document.getElementById("editRewardName").value,
        deskripsi: document.getElementById("editRewardDesc").value,
        kategori: document.getElementById("editRewardCategory").value,
        jenis_reward: document.getElementById("editRewardCategory").value,
        poin_dibutuhkan: parseInt(
            document.getElementById("editRewardPoints").value,
        ),
        stok: parseInt(document.getElementById("editRewardStock").value),
    };

    if (!rewardData.nama_reward.trim()) {
        showNotification("❌ Nama reward tidak boleh kosong!", "error");
        return;
    }

    const success = await saveRewardToServer(rewardData, true, id);
    if (success) {
        closeEditModal();
    }
}

function closeEditModal() {
    document.getElementById("editRewardModal").style.display = "none";
    document.body.style.overflow = "";
}

// ========== ADD REWARD ==========
function openAddRewardModal() {
    document.getElementById("addRewardName").value = "";
    document.getElementById("addRewardDesc").value = "";
    document.getElementById("addRewardCategory").value = "Makanan";
    document.getElementById("addRewardPoints").value = 500;
    document.getElementById("addRewardStock").value = 10;
    document.getElementById("addRewardModal").style.display = "block";
    document.body.style.overflow = "hidden";
}

function closeAddModal() {
    document.getElementById("addRewardModal").style.display = "none";
    document.body.style.overflow = "";
}

async function addNewReward() {
    const rewardData = {
        nama_reward: document.getElementById("addRewardName").value,
        deskripsi: document.getElementById("addRewardDesc").value,
        kategori: document.getElementById("addRewardCategory").value,
        jenis_reward: document.getElementById("addRewardCategory").value,
        poin_dibutuhkan: parseInt(
            document.getElementById("addRewardPoints").value,
        ),
        stok: parseInt(document.getElementById("addRewardStock").value),
    };

    if (!rewardData.nama_reward.trim()) {
        showNotification("❌ Nama reward tidak boleh kosong!", "error");
        return;
    }

    const success = await saveRewardToServer(rewardData, false);
    if (success) {
        closeAddModal();
    }
}

// ========== DELETE REWARD ==========
async function deleteReward(rewardId) {
    const reward = rewardsData.find((r) => r.id_reward === rewardId);
    if (
        reward &&
        confirm(
            `Apakah Anda yakin ingin menghapus reward "${reward.nama_reward}"?`,
        )
    ) {
        await deleteRewardFromServer(rewardId);
    }
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
    setTimeout(() => notification.remove(), 4000);
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

    if (navToggle) navToggle.addEventListener("click", toggleNav);
    if (overlay) overlay.addEventListener("click", toggleNav);

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            showNotification("🚪 Anda telah logout!", "info");
            setTimeout(() => (window.location.href = "/logout"), 1000);
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

    document
        .querySelector(".modal-close")
        ?.addEventListener("click", closeEditModal);
    document
        .querySelector(".modal-close-add")
        ?.addEventListener("click", closeAddModal);
    document
        .getElementById("cancelEditBtn")
        ?.addEventListener("click", closeEditModal);
    document
        .getElementById("cancelAddBtn")
        ?.addEventListener("click", closeAddModal);
    document
        .getElementById("addRewardBtn")
        ?.addEventListener("click", openAddRewardModal);

    document
        .getElementById("editRewardForm")
        ?.addEventListener("submit", (e) => {
            e.preventDefault();
            saveRewardChanges();
        });

    document
        .getElementById("addRewardForm")
        ?.addEventListener("submit", (e) => {
            e.preventDefault();
            addNewReward();
        });

    window.addEventListener("click", (e) => {
        if (e.target === document.getElementById("editRewardModal"))
            closeEditModal();
        if (e.target === document.getElementById("addRewardModal"))
            closeAddModal();
    });
}

// ========== INIT ==========
function init() {
    loadDataFromServer();
    initEventListeners();
}

document.addEventListener("DOMContentLoaded", init);
