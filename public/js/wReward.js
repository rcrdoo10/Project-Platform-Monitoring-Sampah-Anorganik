let totalPoin = window.userData?.poin || 0;
let selectedReward = null;
let selectedQuantity = 1;
let currentCategoryFilter = "all";
let currentHistoryFilter = "all";
let rewardsData = [];
let formattedRewards = [];

let userExchangeHistory = [];
let fullHistoryData = []; 

function showNotification(message, type = "success") {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;

    let icon = "fa-check-circle";
    if (type === "error") icon = "fa-exclamation-circle";
    if (type === "info") icon = "fa-info-circle";

    notification.innerHTML = `<i class="fas ${icon}"></i><span>${message}</span>`;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = "slideOutRight 0.3s ease";
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

function loadDataFromWindow() {
    if (window.rewardsData && window.rewardsData.length > 0) {
        rewardsData = window.rewardsData;
        formatRewards();
        renderRewards();
    } else {
        fetchRewardsFromServer();
    }
}

async function fetchRewardsFromServer() {
    try {
        const response = await fetch("/api/reward/all");
        const data = await response.json();

        if (data.success && data.data && data.data.length > 0) {
            rewardsData = data.data;
        } else {
            rewardsData = [];
            showNotification("Belum ada reward yang tersedia", "info");
        }

        formatRewards();
        renderRewards();
    } catch (error) {
        console.error("Error fetching rewards:", error);
        rewardsData = [];
        showNotification("Gagal memuat data reward", "error");
        formatRewards();
        renderRewards();
    }
}

function formatRewards() {
    if (!rewardsData || rewardsData.length === 0) {
        formattedRewards = [];
        return;
    }

    formattedRewards = rewardsData.map((item) => ({
        id: Number(item.id_reward || item.id),
        name: item.nama_reward || item.name,
        points: Number(item.poin_dibutuhkan || item.points),
        stock: Number(item.stok || item.stock),
        category: item.jenis_reward || item.kategori || item.category,
        desc: item.deskripsi || item.desc,
    }));
}

function updatePoinDisplay() {
    const rewardPoinDisplay = document.getElementById("rewardPoinDisplay");
    if (rewardPoinDisplay) {
        rewardPoinDisplay.innerText = totalPoin.toLocaleString("id-ID");
    }
}

async function fetchExchangeHistory() {
    try {
        const response = await fetch("/api/user/exchange-history");
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            userExchangeHistory = data.data.map((item) => ({
                id: item.id,
                tanggal: item.tanggal,
                reward: item.reward,
                jumlah: item.jumlah,
                totalPoin: item.total_poin,
                status: item.status,
            }));
        } else {
            userExchangeHistory = [];
        }

        renderUserExchangeHistory();
    } catch (error) {
        console.error("Error fetching history:", error);
        userExchangeHistory = [];
        renderUserExchangeHistory();
    }
}

async function fetchAllHistoryReward() {
    const loadingIndicator = document.getElementById("historyLoadingIndicator");
    if (loadingIndicator) loadingIndicator.style.display = "block";

    try {
        const response = await fetch("/api/history/reward/all");
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            fullHistoryData = data.data.map((item) => ({
                id: item.id,
                tanggal: item.tanggal,
                reward: item.reward,
                jumlah: item.jumlah,
                totalPoin: item.total_poin,
                status: item.status,
            }));
        } else {
            fullHistoryData = [];
        }

        return fullHistoryData;
    } catch (error) {
        console.error("Error fetching all history:", error);
        fullHistoryData = [];
        return [];
    } finally {
        if (loadingIndicator) loadingIndicator.style.display = "none";
    }
}

function renderFullHistoryModal() {
    const modalContent = document.getElementById("fullHistoryContent");
    if (!modalContent) return;

    if (fullHistoryData.length === 0) {
        modalContent.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                <p style="margin-top: 16px;">Belum ada riwayat penukaran</p>
            </div>
        `;
        return;
    }

    let html = `
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e9edf2;">
                        <th style="padding: 12px; text-align: left;">Tanggal</th>
                        <th style="padding: 12px; text-align: left;">Reward</th>
                        <th style="padding: 12px; text-align: center;">Jumlah</th>
                        <th style="padding: 12px; text-align: center;">Total Poin</th>
                        <th style="padding: 12px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
    `;

    fullHistoryData.forEach((item) => {
        let statusBadge = "";
        if (item.status === "pending") {
            statusBadge =
                '<span class="status-badge-pending"><i class="fas fa-clock"></i> Menunggu Verifikasi</span>';
        } else if (item.status === "disetujui") {
            statusBadge =
                '<span class="status-badge-approved"><i class="fas fa-check-circle"></i> Disetujui</span>';
        } else {
            statusBadge =
                '<span class="status-badge-pending">' + item.status + "</span>";
        }

        html += `
            <tr style="border-bottom: 1px solid #e9edf2;">
                <td style="padding: 12px;">${item.tanggal}</td>
                <td style="padding: 12px;"><strong style="color:#2e7d64;">${item.reward}</strong></td>
                <td style="padding: 12px; text-align: center;">${item.jumlah} x</td>
                <td style="padding: 12px; text-align: center;"><span style="color:#e9c46a; font-weight:600;">${item.totalPoin.toLocaleString()} poin</span></td>
                <td style="padding: 12px; text-align: center;">${statusBadge}</td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    modalContent.innerHTML = html;
}

async function openFullHistoryModal() {
    const modal = document.getElementById("fullHistoryModal");
    if (!modal) return;

    // Tampilkan loading di modal
    const modalContent = document.getElementById("fullHistoryContent");
    if (modalContent) {
        modalContent.innerHTML =
            '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Memuat semua riwayat...</div>';
    }

    modal.style.display = "block";
    document.body.style.overflow = "hidden";

    // Ambil data lengkap dari server
    await fetchAllHistoryReward();

    // Render data ke modal
    renderFullHistoryModal();
}


function closeFullHistoryModal() {
    const modal = document.getElementById("fullHistoryModal");
    if (modal) {
        modal.style.display = "none";
        document.body.style.overflow = "";
    }
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

    if (filteredData.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" style="text-align:center;">Belum ada riwayat penukaran</td></tr>';
        return;
    }

    filteredData.forEach((item) => {
        const row = tbody.insertRow();
        row.insertCell(0).innerText = item.tanggal;
        row.insertCell(1).innerHTML =
            `<strong style="color:#2e7d64;">${item.reward}</strong>`;
        row.insertCell(2).innerHTML = `${item.jumlah} x`;
        row.insertCell(3).innerHTML =
            `<span style="color:#e9c46a; font-weight:600;">${item.totalPoin.toLocaleString()} poin</span>`;

        // Status badge dengan enum yang benar
        if (item.status === "pending") {
            row.insertCell(4).innerHTML = `<span class="status-badge-pending"><i class="fas fa-clock"></i> Menunggu Verifikasi</span>`;
        } else if (item.status === "approved" || item.status === "disetujui") {  // ← Tambahkan "approved"
            row.insertCell(4).innerHTML = `<span class="status-badge-approved"><i class="fas fa-check-circle"></i> Disetujui</span>`;
        } else {
            row.insertCell(4).innerHTML = `<span class="status-badge-pending">${item.status}</span>`;
        }
    });
}

function renderRewards() {
    const container = document.getElementById("rewardItemsContainer");
    if (!container) return;

    let filteredRewards = formattedRewards;

    if (currentCategoryFilter !== "all") {
        filteredRewards = formattedRewards.filter((reward) => {
            return reward.category === currentCategoryFilter;
        });
    }

    container.innerHTML = "";

    if (filteredRewards.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:40px;">Belum ada reward tersedia untuk kategori ini</div>';
        return;
    }

    filteredRewards.forEach((reward) => {
        const stock = Number(reward.stock) || 0;
        const points = Number(reward.points) || 0;
        const name = reward.name || 'Reward';
        const category = reward.category || 'barang';
        const desc = reward.desc || '';
        
        const isLowStock = stock > 0 && stock <= 5;
        const isOutOfStock = stock <= 0;
        const stockDisplay = isOutOfStock ? "Habis" : `${stock} tersisa`;
        
        let stockClass = '';
        if (isOutOfStock) {
            stockClass = 'out-of-stock';
        } else if (isLowStock) {
            stockClass = 'low-stock';
        }

        let categoryClass = "";
        let iconName = "";
        let categoryDisplay = "";

        if (category === "makanan") {
            categoryClass = "food";
            iconName = "fa-utensils";
            categoryDisplay = "Makanan";
        } else if (category === "minuman") {
            categoryClass = "drink";
            iconName = "fa-mug-hot";
            categoryDisplay = "Minuman";
        } else {
            categoryClass = "goods";
            iconName = "fa-box";
            categoryDisplay = "Barang";
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
                    <h4>${name}</h4>
                    <p>Kategori: ${categoryDisplay} ${desc ? "- " + desc : ""}</p>
                </div>
            </div>
            <div class="reward-stock ${stockClass}">
                <i class="fas fa-boxes"></i>
                <span>Stok: ${stockDisplay}</span>
            </div>
            <div class="reward-price">
                <i class="fas fa-star"></i> ${points.toLocaleString()} poin/unit
            </div>
            <div class="exchange-control">
                <div class="quantity-selector">
                    <button class="qty-minus" data-id="${reward.id}" ${isOutOfStock ? "disabled" : ""}>-</button>
                    <span id="qty-${reward.id}">1</span>
                    <button class="qty-plus" data-id="${reward.id}" ${isOutOfStock ? "disabled" : ""}>+</button>
                </div>
                <div class="total-points">
                    Total: <strong id="total-${reward.id}">${points.toLocaleString()}</strong> poin
                </div>
                <button class="exchange-btn" data-id="${reward.id}" ${isOutOfStock ? "disabled" : ""}>
                    <i class="fas fa-exchange-alt"></i> Tukar
                </button>
            </div>
        `;
        container.appendChild(rewardDiv);
    });


    filteredRewards.forEach((reward) => {
        const stock = Number(reward.stock) || 0;
        const points = Number(reward.points) || 0;
        
        const minusBtn = document.querySelector(`.qty-minus[data-id="${reward.id}"]`);
        const plusBtn = document.querySelector(`.qty-plus[data-id="${reward.id}"]`);
        const qtySpan = document.getElementById(`qty-${reward.id}`);
        const totalSpan = document.getElementById(`total-${reward.id}`);

        if (minusBtn) {
            minusBtn.addEventListener("click", () => {
                let currentQty = parseInt(qtySpan.innerText);
                if (currentQty > 1) {
                    currentQty--;
                    qtySpan.innerText = currentQty;
                    totalSpan.innerText = (points * currentQty).toLocaleString();
                }
            });
        }

        if (plusBtn) {
            plusBtn.addEventListener("click", () => {
                let currentQty = parseInt(qtySpan.innerText);
                if (currentQty < stock) {
                    currentQty++;
                    qtySpan.innerText = currentQty;
                    totalSpan.innerText = (points * currentQty).toLocaleString();
                } else {
                    if (stock <= 0) {
                        showNotification(`⚠️ Stok reward habis!`, "error");
                    } else {
                        showNotification(`⚠️ Stok maksimal ${stock} item!`, "error");
                    }
                }
            });
        }
    });

    const exchangeBtns = document.querySelectorAll(".exchange-btn");
    exchangeBtns.forEach((btn) => {
        btn.removeEventListener("click", handleExchangeClick);
        btn.addEventListener("click", handleExchangeClick);
    });
}

function handleExchangeClick(e) {
    const btn = e.currentTarget;
    const id = Number(btn.dataset.id);
    const reward = formattedRewards.find((r) => r.id === id);

    if (!reward) {
        showNotification("Reward tidak ditemukan!", "error");
        return;
    }

    const qtySpan = document.getElementById(`qty-${reward.id}`);
    const quantity = parseInt(qtySpan.innerText);
    
    if (reward.stock <= 0) {
        showNotification(`❌ Stok reward habis!`, "error");
        return;
    }
    
    if (reward.stock < quantity) {
        showNotification(`❌ Stok tidak mencukupi! Tersedia ${reward.stock} item.`, "error");
        return;
    }

    const totalPointsNeeded = reward.points * quantity;

    if (totalPoin >= totalPointsNeeded) {
        selectedReward = reward;
        selectedQuantity = quantity;
        
        const confirmMessage = document.getElementById("confirmMessage");
        if (confirmMessage) {
            confirmMessage.innerHTML = `
                Anda akan menukarkan <strong>${quantity}x ${reward.name}</strong><br>
                Total poin yang dibutuhkan: <strong style="color:#e9c46a;">${totalPointsNeeded.toLocaleString()} poin</strong><br>
                Stok setelah ditukar: <strong>${reward.stock - quantity}</strong>
            `;
        }
        
        const modal = document.getElementById("confirmModal");
        if (modal) {
            modal.style.display = "block";
            document.body.style.overflow = "hidden";
        }
    } else {
        showNotification(`❌ Poin tidak mencukupi! Butuh ${totalPointsNeeded.toLocaleString()} poin, Anda memiliki ${totalPoin.toLocaleString()} poin.`, "error");
    }
}

async function processExchange() {
    if (selectedReward && selectedQuantity) {
        const totalPointsNeeded = selectedReward.points * selectedQuantity;

        const confirmBtn = document.getElementById("confirmExchangeBtn");
        const originalText = confirmBtn.innerHTML;
        confirmBtn.innerHTML =
            '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        confirmBtn.disabled = true;

        try {
            const response = await fetch("/api/exchange-reward", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
                body: JSON.stringify({
                    reward_id: selectedReward.id,
                    quantity: selectedQuantity,
                    total_points: totalPointsNeeded,
                }),
            });

            const data = await response.json();

            if (data.success) {
                totalPoin = data.remaining_poin;
                updatePoinDisplay();

                const rewardIndex = formattedRewards.findIndex(
                    (r) => r.id === selectedReward.id,
                );
                if (rewardIndex !== -1) {
                    formattedRewards[rewardIndex].stock = data.reward_stock;
                }

                await fetchExchangeHistory();
                renderRewards();

                showNotification(
                    `🎉 Permintaan penukaran ${selectedQuantity}x ${selectedReward.name} berhasil! Menunggu verifikasi admin.`,
                    "success",
                );
                closeConfirmModal();
            } else {
                showNotification(`❌ ${data.message}`, "error");
            }
        } catch (error) {
            console.error("Error:", error);
            showNotification(
                "❌ Terjadi kesalahan, silakan coba lagi!",
                "error",
            );
        } finally {
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = false;
        }

        selectedReward = null;
        selectedQuantity = 1;
    }
}

function closeConfirmModal() {
    const modal = document.getElementById("confirmModal");
    if (modal) {
        modal.style.display = "none";
        document.body.style.overflow = "";
    }
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

    // Filter kategori
    const categoryBtns = document.querySelectorAll(".category-btn");
    categoryBtns.forEach((btn) => {
        btn.addEventListener("click", () => {
            categoryBtns.forEach((b) => b.classList.remove("active"));
            btn.classList.add("active");

            const btnCategory = btn.dataset.category;
            if (btnCategory === "food") {
                currentCategoryFilter = "makanan";
            } else if (btnCategory === "drink") {
                currentCategoryFilter = "minuman";
            } else if (btnCategory === "goods") {
                currentCategoryFilter = "barang";
            } else {
                currentCategoryFilter = "all";
            }

            renderRewards();
        });
    });

    // Filter status riwayat
    const filterBtns = document.querySelectorAll(".status-filter-btn");
    filterBtns.forEach((btn) => {
        btn.addEventListener("click", () => {
            filterBtns.forEach((b) => b.classList.remove("active"));
            btn.classList.add("active");
            currentHistoryFilter = btn.dataset.filter;
            renderUserExchangeHistory();
        });
    });

    // Tombol Lihat Semua Riwayat
    const lihatSemuaBtn = document.getElementById("lihatSemuaHistoryBtn");
    if (lihatSemuaBtn) {
        lihatSemuaBtn.addEventListener("click", openFullHistoryModal);
    }

    const closeFullHistoryBtn = document.getElementById(
        "closeFullHistoryModal",
    );
    if (closeFullHistoryBtn) {
        closeFullHistoryBtn.addEventListener("click", closeFullHistoryModal);
    }

    const modalClose = document.querySelector("#confirmModal .modal-close");
    const cancelConfirmBtn = document.getElementById("cancelConfirmBtn");
    const confirmExchangeBtn = document.getElementById("confirmExchangeBtn");

    if (modalClose) modalClose.addEventListener("click", closeConfirmModal);
    if (cancelConfirmBtn)
        cancelConfirmBtn.addEventListener("click", closeConfirmModal);
    if (confirmExchangeBtn)
        confirmExchangeBtn.addEventListener("click", processExchange);

    // Tutup modal saat klik di luar
    window.addEventListener("click", (e) => {
        const confirmModal = document.getElementById("confirmModal");
        if (e.target === confirmModal) closeConfirmModal();

        const fullHistoryModal = document.getElementById("fullHistoryModal");
        if (e.target === fullHistoryModal) closeFullHistoryModal();
    });
}

async function init() {
    updatePoinDisplay();

    if (window.rewardsData && window.rewardsData.length > 0) {
        rewardsData = window.rewardsData;
        formatRewards();
        renderRewards();
    } else {
        await fetchRewardsFromServer();
    }

    await fetchExchangeHistory();
    initEventListeners();
}

document.addEventListener("DOMContentLoaded", init);
