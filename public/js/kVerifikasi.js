let currentStatus = "pending";

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute("content");
    return "";
}

function showNotification(message, type = "success") {
    const oldNotif = document.querySelector(".custom-notification");
    if (oldNotif) oldNotif.remove();

    const notification = document.createElement("div");
    notification.className = "custom-notification";
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 12px;
        z-index: 9999;
        animation: slideInRight 0.3s ease;
        background: ${type === "success" ? "#2e7d64" : "#b6695a"};
        color: white;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    notification.innerHTML = `<i class="fas ${type === "success" ? "fa-check-circle" : "fa-exclamation-circle"}"></i> ${message}`;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = "slideOutRight 0.3s ease";
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

function showButtonLoading(button, isLoading, originalText = null) {
    if (!button) return;
    
    if (isLoading) {
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        button.disabled = true;
        button.style.opacity = "0.7";
        button.style.cursor = "wait";
    } else {
        if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
        } else if (originalText) {
            button.innerHTML = originalText;
        }
        button.disabled = false;
        button.style.opacity = "1";
        button.style.cursor = "pointer";
    }
}

function showConfirmDialog(message, title = "Konfirmasi") {
    return new Promise((resolve) => {
        const modal = document.createElement("div");
        modal.className = "custom-confirm-modal";
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(4px);
        `;
        
        modal.innerHTML = `
            <div style="background: white; border-radius: 24px; max-width: 400px; width: 90%; padding: 24px; text-align: center; animation: modalSlideIn 0.3s ease;">
                <i class="fas fa-question-circle" style="font-size: 48px; color: #e9c46a; margin-bottom: 16px;"></i>
                <h3 style="margin-bottom: 12px; color: #1e2f3a;">${title}</h3>
                <p style="margin-bottom: 24px; color: #5f7c8b;">${message}</p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button class="confirm-no" style="background: #eef2f5; border: none; padding: 10px 24px; border-radius: 40px; cursor: pointer; font-weight: 500;">Batal</button>
                    <button class="confirm-yes" style="background: #2e7d64; color: white; border: none; padding: 10px 24px; border-radius: 40px; cursor: pointer; font-weight: 500;">Ya, Setujui</button>
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

async function fetchTransactions(status) {
    const tbody = document.getElementById("verifikasiBody");
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;"><i class="fas fa-spinner fa-spin"></i> Memuat data...<\/td><\/tr>';
    }

    try {
        let url = status === "pending" ? "/api/transaksi/pending" : "/api/transaksi/approved";

        const response = await fetch(url, {
            method: "GET",
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "same-origin",
        });

        const data = await response.json();

        if (data.success) {
            renderTransactions(data.data, status);
            updateCounters(data.data.length, status);
        } else {
            if (data.message === "Unauthorized") {
                showNotification("Sesi habis, silakan login kembali", "error");
                setTimeout(() => { window.location.href = "/login"; }, 2000);
            } else {
                showNotification(data.message || "Gagal memuat data", "error");
            }
        }
    } catch (error) {
        console.error("Error:", error);
        showNotification("Terjadi kesalahan", "error");
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Gagal memuat数据<\/td><\/tr>';
        }
    }
}

function renderTransactions(transactions, status) {
    const tbody = document.getElementById("verifikasiBody");
    if (!tbody) return;

    if (transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Tidak ada transaksi<\/td><\/tr>';
        return;
    }

    tbody.innerHTML = "";

    transactions.forEach((trans) => {
        const row = tbody.insertRow();

        row.insertCell(0).innerHTML = `${trans.tanggal_transaksi}<br><small style="color:#999;">${trans.jam_transaksi || "00:00"}</small>`;
        row.cells[0].style.textAlign = "center";

        row.insertCell(1).innerHTML = `<strong>${trans.user?.id_user || "-"}</strong>`;
        row.cells[1].style.textAlign = "center";

        row.insertCell(2).innerHTML = `<strong>${trans.user?.nama || "User"}</strong>`;
        row.cells[2].style.textAlign = "center";

        row.insertCell(3).innerHTML = `${trans.jumlah_transaksi || 1} x`;
        row.cells[3].style.textAlign = "center";

        row.insertCell(4).innerHTML = `<span style="color:#e9c46a; font-weight:600;">${Number(trans.poin_transaksi).toLocaleString("id-ID")} poin</span>`;
        row.cells[4].style.textAlign = "center";

        if (status === "pending") {
            row.insertCell(5).innerHTML = '<span class="status-badge-pending"><i class="fas fa-clock"></i> Pending</span>';
        } else {
            row.insertCell(5).innerHTML = '<span class="status-badge-approved"><i class="fas fa-check-circle"></i> Disetujui</span>';
        }
        row.cells[5].style.textAlign = "center";

        const actionCell = row.insertCell(6);
        actionCell.style.textAlign = "center";

        if (status === "pending") {
            actionCell.innerHTML = `
                <button class="approve-btn" data-id="${trans.id_transaksi}" data-name="${trans.user?.nama || "User"}">
                    <i class="fas fa-check-circle"></i> Setujui
                </button>
                <button class="detail-btn" data-trans='${JSON.stringify(trans).replace(/'/g, "&#39;")}'>
                    <i class="fas fa-info-circle"></i> Detail
                </button>
            `;
        } else {
            actionCell.innerHTML = `
                <button class="detail-btn" data-trans='${JSON.stringify(trans).replace(/'/g, "&#39;")}'>
                    <i class="fas fa-info-circle"></i> Detail
                </button>
            `;
        }
    });

    document.querySelectorAll(".approve-btn").forEach(btn => {
        btn.removeEventListener("click", handleApprove);
        btn.addEventListener("click", handleApprove);
    });

    document.querySelectorAll(".detail-btn").forEach((btn) => {
        btn.removeEventListener("click", handleDetail);
        btn.addEventListener("click", handleDetail);
    });
}

async function handleApprove(e) {
    const btn = e.currentTarget;
    const id = btn.dataset.id;
    const userName = btn.dataset.name;
    
    const confirmed = await showConfirmDialog(
        `Setujui transaksi dari <strong>${userName}</strong>?`,
        "Verifikasi Transaksi"
    );
    
    if (!confirmed) return;
    
    const originalText = btn.innerHTML;
    showButtonLoading(btn, true, originalText);
    
    try {
        const csrfToken = getCsrfToken();
        
        const response = await fetch(`/api/transaksi/${id}/approve`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const row = btn.closest('tr');
            row.style.backgroundColor = '#dff0e6';
            row.style.transition = 'background-color 0.3s ease';
            
            showNotification(`✅ Transaksi dari ${userName} berhasil disetujui!`, "success");
            
            setTimeout(() => {
                fetchTransactions(currentStatus);
                refreshAllCounters();
            }, 1000);
        } else {
            showNotification(data.message || "Gagal menyetujui transaksi", "error");
            showButtonLoading(btn, false, originalText);
        }
    } catch (error) {
        console.error("Error:", error);
        showNotification("Terjadi kesalahan pada server", "error");
        showButtonLoading(btn, false, originalText);
    }
}

function handleDetail(e) {
    const btn = e.currentTarget;
    const transData = JSON.parse(btn.dataset.trans);

    btn.style.transform = "scale(0.95)";
    setTimeout(() => {
        btn.style.transform = "scale(1)";
    }, 150);
    
    openDetailModal(transData);
}

function openDetailModal(trans) {
    document.getElementById("detailId").innerText = trans.id_transaksi || "-";
    document.getElementById("detailIdUser").innerText = trans.user?.id_user || "-";
    document.getElementById("detailNama").innerText = trans.user?.nama || "-";
    document.getElementById("detailTanggal").innerText = trans.tanggal_transaksi || "-";
    document.getElementById("detailJam").innerText = trans.jam_transaksi || "-";
    document.getElementById("detailJumlah").innerText = trans.jumlah_transaksi || "1";
    document.getElementById("detailPoin").innerText = Number(trans.poin_transaksi).toLocaleString("id-ID") + " poin";

    const statusSpan = document.getElementById("detailStatus");
    if (trans.status_transaksi == 0 || trans.status_transaksi === "pending") {
        statusSpan.innerHTML = '<span class="status-badge-pending">⏳ Pending</span>';
    } else {
        statusSpan.innerHTML = '<span class="status-badge-approved">✓ Disetujui</span>';
    }

    document.getElementById("detailTransaksiModal").style.display = "block";
    document.body.style.overflow = "hidden";
}

function closeDetailModal() {
    const modal = document.getElementById("detailTransaksiModal");
    modal.style.animation = "modalSlideOut 0.2s ease";
    setTimeout(() => {
        modal.style.display = "none";
        modal.style.animation = "";
        document.body.style.overflow = "";
    }, 200);
}

function updateCounters(count, status) {
    if (status === "pending") {
        const pendingSpan = document.getElementById("pendingCount");
        if (pendingSpan) pendingSpan.innerText = count;
    } else {
        const approvedSpan = document.getElementById("approvedCount");
        if (approvedSpan) approvedSpan.innerText = count;
    }
}

async function refreshAllCounters() {
    try {
        const pendingRes = await fetch("/api/transaksi/pending", { credentials: "same-origin" });
        const pendingData = await pendingRes.json();
        if (pendingData.success) {
            const pendingSpan = document.getElementById("pendingCount");
            if (pendingSpan) pendingSpan.innerText = pendingData.data.length;
        }

        const approvedRes = await fetch("/api/transaksi/approved", { credentials: "same-origin" });
        const approvedData = await approvedRes.json();
        if (approvedData.success) {
            const approvedSpan = document.getElementById("approvedCount");
            if (approvedSpan) approvedSpan.innerText = approvedData.data.length;
        }
    } catch (error) {
        console.error("Error refreshing counters:", error);
    }
}

function initEventListeners() {
    const tabBtns = document.querySelectorAll(".tab-btn");
    tabBtns.forEach((btn) => {
        btn.addEventListener("click", () => {
            tabBtns.forEach((b) => b.classList.remove("active"));
            btn.classList.add("active");

            currentStatus = btn.dataset.status;
            const tableTitle = document.getElementById("tableTitle");
            if (tableTitle) {
                tableTitle.innerText = currentStatus === "pending"
                    ? "Transaksi Menunggu Verifikasi"
                    : "Riwayat Transaksi Disetujui";
            }

            fetchTransactions(currentStatus);
        });
    });

    const closeDetailBtn = document.querySelector(".modal-close-detail, .close-detail-btn");
    if (closeDetailBtn) {
        closeDetailBtn.addEventListener("click", closeDetailModal);
    }

    window.addEventListener("click", (e) => {
        const detailModal = document.getElementById("detailTransaksiModal");
        if (e.target === detailModal) closeDetailModal();
    });
}

async function init() {
    initEventListeners();
    await fetchTransactions("pending");
    await refreshAllCounters();
}

document.addEventListener("DOMContentLoaded", init);