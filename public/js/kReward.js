let rewardsData = [];
let currentCategoryFilter = "all";

function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute("content") : "";
}

function showNotification(message, type = "success") {
    const notification = document.createElement("div");
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
        animation: slideInRight 0.3s ease;
    `;
    notification.innerHTML = `<i class="fas ${type === "success" ? "fa-check-circle" : "fa-exclamation-circle"}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.animation = "slideOutRight 0.3s ease";
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

async function loadRewards() {
    try {
        const res = await fetch("/api/reward/all");
        const data = await res.json();
        if (data.success) {
            rewardsData = data.data;
            renderRewards();
        }
    } catch (error) {
        console.error(error);
        showNotification("Gagal memuat data!", "error");
    }
}

function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function showConfirmDeleteDialog(rewardName, title = "Konfirmasi Hapus") {
    return new Promise((resolve) => {
        const modal = document.createElement("div");
        modal.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: flex; align-items: center;
            justify-content: center; z-index: 10000; backdrop-filter: blur(4px);
        `;

        modal.innerHTML = `
            <div style="background: white; border-radius: 24px; max-width: 400px; width: 90%; padding: 24px; text-align: center;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #e74c3c; margin-bottom: 16px;"></i>
                <h3 style="margin-bottom: 12px; color: #1e2f3a;">${title}</h3>
                <p style="margin-bottom: 24px; color: #5f7c8b;">Yakin hapus reward <strong>${escapeHtml(rewardName)}</strong>?</p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button class="confirm-no" style="background: #eef2f5; border: none; padding: 10px 24px; border-radius: 40px; cursor: pointer; font-weight: 500;">Batal</button>
                    <button class="confirm-yes" style="background: #e74c3c; color: white; border: none; padding: 10px 24px; border-radius: 40px; cursor: pointer; font-weight: 500;">Ya, Hapus</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        modal.querySelector(".confirm-yes").onclick = () => {
            modal.remove();
            resolve(true);
        };
        modal.querySelector(".confirm-no").onclick = () => {
            modal.remove();
            resolve(false);
        };
        modal.onclick = (e) => {
            if (e.target === modal) {
                modal.remove();
                resolve(false);
            }
        };
    });
}

function renderRewards() {
    const container = document.getElementById("rewardItemsContainer");
    if (!container) return;

    let filtered = rewardsData;
    if (currentCategoryFilter !== "all") {
        filtered = rewardsData.filter(
            (r) => r.jenis_reward === currentCategoryFilter,
        );
    }

    container.innerHTML = "";
    if (filtered.length === 0) {
        container.innerHTML = '<div class="no-data">Belum ada reward</div>';
        return;
    }

    filtered.forEach((reward) => {
        const satuanTeks = reward.kategori === "lusin" ? " Lusin" : " PCS";
        const stock = Number(reward.stok) || 0;
        const isLowStock = stock > 0 && stock <= 5;
        const isOutOfStock = stock <= 0;
        const stockDisplay = isOutOfStock ? "Habis" : `${stock}${satuanTeks}`;

        let stockClass = "";
        if (isOutOfStock) stockClass = "out-of-stock";
        else if (isLowStock) stockClass = "low-stock";

        let iconClass = "goods";
        let iconName = "fa-box";
        if (reward.jenis_reward === "makanan") {
            iconClass = "food";
            iconName = "fa-utensils";
        } else if (reward.jenis_reward === "minuman") {
            iconClass = "drink";
            iconName = "fa-mug-hot";
        }

        // ✅ GAMBAR: pakai file dari storage kalau ada, kalau tidak pakai ikon
        const hasImage =
            reward.gambar_reward && reward.gambar_reward !== "default.jpg";
        const iconHtml = hasImage
            ? `<img src="/storage/rewards/${reward.gambar_reward}" alt="${escapeHtml(reward.nama_reward)}"
                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 20px;">`
            : `<i class="fas ${iconName}"></i>`;

        const div = document.createElement("div");
        div.className = "reward-item";
        div.setAttribute("data-id", reward.id_reward);
        div.innerHTML = `
            <div class="reward-info">
                <div class="reward-icon ${iconClass}">
                    ${iconHtml}
                </div>
                <div class="reward-details">
                    <h4>${escapeHtml(reward.nama_reward)}</h4>
                    <p>${escapeHtml(reward.deskripsi || "Reward menarik")}</p>
                </div>
            </div>
            <div class="reward-stats">
                <div class="reward-points">
                    <i class="fas fa-star"></i> ${Number(reward.poin_dibutuhkan).toLocaleString()} poin
                </div>
                <div class="reward-stock ${stockClass}">
                    <i class="fas fa-boxes"></i> Stok: ${stockDisplay}
                </div>
            </div>
            <div class="reward-actions">
                <button class="edit-btn" data-id="${reward.id_reward}">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="delete-btn" data-id="${reward.id_reward}">
                    <i class="fas fa-trash-alt"></i> Hapus
                </button>
            </div>
        `;
        container.appendChild(div);
    });

    document.querySelectorAll(".edit-btn").forEach((btn) => {
        btn.onclick = function () {
            openEditModal(this.dataset.id);
        };
    });

    document.querySelectorAll(".delete-btn").forEach((btn) => {
        btn.onclick = function () {
            deleteReward(this.dataset.id);
        };
    });
}

function openEditModal(id) {
    const reward = rewardsData.find((r) => r.id_reward == id);
    if (!reward) {
        showNotification("Data reward tidak ditemukan!", "error");
        return;
    }

    document.getElementById("editRewardId").value = reward.id_reward;
    document.getElementById("editRewardName").value = reward.nama_reward;
    document.getElementById("editRewardDesc").value = reward.deskripsi || "";

    let jenis = reward.jenis_reward || "makanan";
    if (jenis === "makanan") jenis = "Makanan";
    else if (jenis === "minuman") jenis = "Minuman";
    else jenis = "Barang";

    document.getElementById("editRewardCategory").value = jenis;
    document.getElementById("editRewardSatuan").value =
        reward.kategori || "pcs";
    document.getElementById("editRewardPoints").value = reward.poin_dibutuhkan;
    document.getElementById("editRewardStock").value = reward.stok;

    // ✅ Reset file input & preview
    document.getElementById("editRewardImage").value = "";
    const previewWrap = document.getElementById("editImagePreviewWrap");
    const previewImg = document.getElementById("editImagePreview");

    if (reward.gambar_reward && reward.gambar_reward !== "default.jpg") {
        previewImg.src = `/storage/rewards/${reward.gambar_reward}`;
        previewWrap.style.display = "block";
    } else {
        previewWrap.style.display = "none";
        previewImg.src = "";
    }

    document.getElementById("editRewardModal").style.display = "block";
    document.body.style.overflow = "hidden";
}

function closeEditModal() {
    document.getElementById("editRewardModal").style.display = "none";
    document.body.style.overflow = "";
}

async function saveEdit() {
    const id = document.getElementById("editRewardId").value;
    const fileInput = document.getElementById("editRewardImage");

    // ✅ Pakai FormData untuk upload file
    const formData = new FormData();
    formData.append("_method", "PUT");
    formData.append(
        "nama_reward",
        document.getElementById("editRewardName").value,
    );
    formData.append(
        "deskripsi",
        document.getElementById("editRewardDesc").value,
    );
    formData.append(
        "kategori",
        document.getElementById("editRewardCategory").value,
    );
    formData.append(
        "satuan",
        document.getElementById("editRewardSatuan").value,
    );
    formData.append(
        "poin_dibutuhkan",
        document.getElementById("editRewardPoints").value,
    );
    formData.append("stok", document.getElementById("editRewardStock").value);

    if (fileInput.files.length > 0) {
        formData.append("gambar_reward", fileInput.files[0]);
    }

    if (!document.getElementById("editRewardName").value.trim()) {
        showNotification("Nama reward tidak boleh kosong!", "error");
        return;
    }

    try {
        const res = await fetch(`/api/rewards/${id}`, {
            method: "POST", // ⚠️ POST karena FormData + PUT tidak kompatibel di PHP
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                Accept: "application/json",
            },
            body: formData,
        });

        const result = await res.json();
        if (result.success) {
            showNotification("Reward berhasil diupdate!");
            closeEditModal();
            loadRewards();
        } else {
            showNotification(result.message || "Gagal update!", "error");
        }
    } catch (error) {
        console.error(error);
        showNotification("Terjadi kesalahan: " + error.message, "error");
    }
}

function openAddModal() {
    document.getElementById("addRewardName").value = "";
    document.getElementById("addRewardDesc").value = "";
    document.getElementById("addRewardCategory").value = "Makanan";
    document.getElementById("addRewardSatuan").value = "pcs";
    document.getElementById("addRewardPoints").value = 500;
    document.getElementById("addRewardStock").value = 10;

    // ✅ Reset file input & preview
    document.getElementById("addRewardImage").value = "";
    document.getElementById("addImagePreviewWrap").style.display = "none";
    document.getElementById("addImagePreview").src = "";

    document.getElementById("addRewardModal").style.display = "block";
    document.body.style.overflow = "hidden";
}

function closeAddModal() {
    document.getElementById("addRewardModal").style.display = "none";
    document.body.style.overflow = "";
}

async function saveAdd() {
    const fileInput = document.getElementById("addRewardImage");

    // ✅ Pakai FormData untuk upload file
    const formData = new FormData();
    formData.append(
        "nama_reward",
        document.getElementById("addRewardName").value,
    );
    formData.append(
        "deskripsi",
        document.getElementById("addRewardDesc").value,
    );
    formData.append(
        "kategori",
        document.getElementById("addRewardCategory").value,
    );
    formData.append("satuan", document.getElementById("addRewardSatuan").value);
    formData.append(
        "poin_dibutuhkan",
        document.getElementById("addRewardPoints").value,
    );
    formData.append("stok", document.getElementById("addRewardStock").value);

    if (fileInput.files.length > 0) {
        formData.append("gambar_reward", fileInput.files[0]);
    }

    if (!document.getElementById("addRewardName").value.trim()) {
        showNotification("Nama reward tidak boleh kosong!", "error");
        return;
    }

    try {
        const res = await fetch("/api/rewards", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                Accept: "application/json",
            },
            body: formData,
        });

        const result = await res.json();
        if (result.success) {
            showNotification("Reward berhasil ditambahkan!");
            closeAddModal();
            loadRewards();
        } else {
            showNotification(result.message || "Gagal tambah!", "error");
        }
    } catch (error) {
        console.error(error);
        showNotification("Terjadi kesalahan: " + error.message, "error");
    }
}

async function deleteReward(id) {
    const reward = rewardsData.find((r) => r.id_reward == id);
    if (!reward) {
        showNotification("Data reward tidak ditemukan!", "error");
        return;
    }

    const confirmed = await showConfirmDeleteDialog(
        reward.nama_reward,
        "Hapus Reward",
    );
    if (!confirmed) return;

    try {
        const res = await fetch(`/api/rewards/${id}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                Accept: "application/json",
            },
        });

        const result = await res.json();

        if (result.success) {
            showNotification("Reward berhasil dihapus!", "success");
            loadRewards();
        } else {
            showNotification(result.message || "Gagal hapus!", "error");
        }
    } catch (error) {
        console.error(error);
        showNotification("Terjadi kesalahan: " + error.message, "error");
    }
}

// ========== PREVIEW GAMBAR ==========
function setupImagePreview(inputId, previewWrapId, previewImgId) {
    const input = document.getElementById(inputId);
    const wrap = document.getElementById(previewWrapId);
    const img = document.getElementById(previewImgId);

    if (!input) return;

    input.addEventListener("change", function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                img.src = ev.target.result;
                wrap.style.display = "block";
            };
            reader.readAsDataURL(file);
        } else {
            wrap.style.display = "none";
            img.src = "";
        }
    });
}

// ========== INIT ==========
document.addEventListener("DOMContentLoaded", function () {
    loadRewards();

    document
        .getElementById("addRewardBtn")
        ?.addEventListener("click", openAddModal);

    document
        .querySelector("#editRewardModal .modal-close")
        ?.addEventListener("click", closeEditModal);
    document
        .querySelector("#addRewardModal .modal-close-add")
        ?.addEventListener("click", closeAddModal);

    document
        .getElementById("cancelEditBtn")
        ?.addEventListener("click", closeEditModal);
    document
        .getElementById("cancelAddBtn")
        ?.addEventListener("click", closeAddModal);

    document
        .getElementById("editRewardForm")
        ?.addEventListener("submit", function (e) {
            e.preventDefault();
            saveEdit();
        });

    document
        .getElementById("addRewardForm")
        ?.addEventListener("submit", function (e) {
            e.preventDefault();
            saveAdd();
        });

    // Filter kategori
    const categoryBtns = document.querySelectorAll(".category-btn");
    categoryBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
            categoryBtns.forEach((b) => b.classList.remove("active"));
            this.classList.add("active");
            currentCategoryFilter = this.dataset.category;
            renderRewards();
        });
    });

    // ✅ Setup preview gambar
    setupImagePreview(
        "editRewardImage",
        "editImagePreviewWrap",
        "editImagePreview",
    );
    setupImagePreview(
        "addRewardImage",
        "addImagePreviewWrap",
        "addImagePreview",
    );

    // Close modal saat klik di luar
    window.addEventListener("click", (e) => {
        const editModal = document.getElementById("editRewardModal");
        const addModal = document.getElementById("addRewardModal");
        if (e.target === editModal) closeEditModal();
        if (e.target === addModal) closeAddModal();
    });
});
