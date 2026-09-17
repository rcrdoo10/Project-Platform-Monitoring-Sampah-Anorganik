console.log("kDashboard.js loaded");

let chartLabels = window.chartLabels || [];
let chartData = window.chartData || [];
let recentTransactions = window.recentTransactions || [];
let allTransactions = window.allTransactions || [];
let currentPeriod = window.currentPeriod || "daily";

let chartInstance = null;

function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function getXAxisTitle() {
    switch (currentPeriod) {
        case "weekly":
            return "Minggu Ke-";
        case "monthly":
            return "Bulan";
        default:
            return "Tanggal";
    }
}

function updatePeriodText() {
    const periodText = document.getElementById("periodText");
    if (!periodText) return;

    switch (currentPeriod) {
        case "weekly":
            periodText.innerHTML = "Periode: 6 Minggu Terakhir";
            break;
        case "monthly":
            periodText.innerHTML = "Periode: 12 Bulan Terakhir";
            break;
        default:
            periodText.innerHTML = "Periode: 7 Hari Terakhir";
    }
}

function initMainChart() {
    const canvas = document.getElementById("wasteChart");
    if (!canvas) {
        console.error("Canvas wasteChart tidak ditemukan!");
        return;
    }

    const ctx = canvas.getContext("2d");

    if (!chartLabels || chartLabels.length === 0) {
        ctx.font = "14px Arial";
        ctx.fillStyle = "#999";
        ctx.textAlign = "center";
        ctx.fillText(
            "Belum ada data transaksi",
            canvas.width / 2,
            canvas.height / 2,
        );
        return;
    }

    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = new Chart(ctx, {
        type: "line",
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: "Total Penukaran (Unit)",
                    data: chartData,
                    borderColor: "#2e7d64",
                    backgroundColor: "rgba(46, 125, 100, 0.1)",
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: "#2e7d64",
                    pointBorderColor: "#ffffff",
                    pointRadius: 5,
                    pointHoverRadius: 7,
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
                            return (
                                "Total Penukaran: " + context.raw + " transaksi"
                            );
                        },
                    },
                },
                legend: {
                    position: "top",
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: "#eef2f5" },
                    title: {
                        display: true,
                        text: "Jumlah Penukaran (Unit)",
                        color: "#5e7c8b",
                    },
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                    },
                },
                x: {
                    grid: { display: false },
                    title: {
                        display: true,
                        text: getXAxisTitle(),
                        color: "#5e7c8b",
                    },
                },
            },
        },
    });
}

function renderHistoryTable() {
    const tbody = document.getElementById("historyBody");
    if (!tbody) return;

    tbody.innerHTML = "";

    if (!recentTransactions || recentTransactions.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" style="text-align: center;">Belum ada transaksi<\/td><\/tr>';
        return;
    }

    recentTransactions.forEach((item) => {
        const row = tbody.insertRow();
        row.insertCell(0).innerText = item.tanggal_transaksi || "-";

        let rewardNames = "";
        if (item.rewards && item.rewards.length > 0) {
            rewardNames = item.rewards.map((r) => r.nama_reward).join(", ");
        } else {
            rewardNames = "-";
        }
        row.insertCell(1).innerHTML =
            '<strong style="color:#2e7d64;">' +
            escapeHtml(rewardNames) +
            "</strong>";
        row.insertCell(2).innerHTML =
            '<span style="color:#e9c46a; font-weight:600;">' +
            Number(item.poin_transaksi).toLocaleString() +
            " poin</span>";

        if (item.status_transaksi == 0) {
            row.insertCell(3).innerHTML =
                '<span class="status-badge-pending">⏳ Pending</span>';
        } else {
            row.insertCell(3).innerHTML =
                '<span class="status-badge-approved">✓ Disetujui</span>';
        }

        const btn = document.createElement("button");
        btn.className = "detail-btn";
        btn.textContent = "Lihat detail";
        btn.dataset.id = item.id_transaksi;
        btn.onclick = function () {
            openDetailModal(item.id_transaksi);
        };
        const cell = row.insertCell(4);
        cell.appendChild(btn);
    });
}

function renderFullHistoryTable() {
    const tbody = document.getElementById("fullHistoryBody");
    if (!tbody) return;

    tbody.innerHTML = "";

    if (!allTransactions || allTransactions.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" style="text-align: center;">Belum ada transaksi<\/td><\/tr>';
        return;
    }

    allTransactions.forEach((item) => {
        const row = tbody.insertRow();
        row.insertCell(0).innerText = item.tanggal_transaksi || "-";

        let rewardNames = "";
        if (item.rewards && item.rewards.length > 0) {
            rewardNames = item.rewards.map((r) => r.nama_reward).join(", ");
        } else {
            rewardNames = "-";
        }
        row.insertCell(1).innerHTML =
            '<strong style="color:#2e7d64;">' +
            escapeHtml(rewardNames) +
            "</strong>";
        row.insertCell(2).innerHTML =
            '<span style="color:#e9c46a; font-weight:600;">' +
            Number(item.poin_transaksi).toLocaleString() +
            " poin</span>";

        if (item.status_transaksi == 0) {
            row.insertCell(3).innerHTML =
                '<span class="status-badge-pending">⏳ Pending</span>';
        } else {
            row.insertCell(3).innerHTML =
                '<span class="status-badge-approved">✓ Disetujui</span>';
        }

        const btn = document.createElement("button");
        btn.className = "detail-btn";
        btn.textContent = "Lihat detail";
        btn.dataset.id = item.id_transaksi;
        btn.onclick = function () {
            openDetailModal(item.id_transaksi);
        };
        const cell = row.insertCell(4);
        cell.appendChild(btn);
    });
}

function openDetailModal(id) {
    const allData = [...(recentTransactions || []), ...(allTransactions || [])];
    const transaction = allData.find(function (t) {
        return t.id_transaksi == id;
    });

    if (!transaction) {
        alert("Data transaksi tidak ditemukan");
        return;
    }

    document.getElementById("detailId").innerText =
        transaction.id_transaksi || "-";
    document.getElementById("detailTanggal").innerText =
        transaction.tanggal_transaksi || "-";
    document.getElementById("detailNama").innerText =
        transaction.user && transaction.user.nama ? transaction.user.nama : "-";

    let rewardNames = "";
    if (transaction.rewards && transaction.rewards.length > 0) {
        rewardNames = transaction.rewards
            .map(function (r) {
                return r.nama_reward;
            })
            .join(", ");
    } else {
        rewardNames = "-";
    }
    document.getElementById("detailReward").innerText = rewardNames;
    document.getElementById("detailPoin").innerText =
        Number(transaction.poin_transaksi).toLocaleString() + " poin";

    const statusSpan = document.getElementById("detailStatus");
    if (transaction.status_transaksi == 0) {
        statusSpan.innerHTML =
            '<span class="status-badge-pending">⏳ Pending</span>';
    } else {
        statusSpan.innerHTML =
            '<span class="status-badge-approved">✓ Disetujui</span>';
    }

    document.getElementById("detailTransaksiModal").style.display = "block";
    document.body.style.overflow = "hidden";
}

function closeDetailModal() {
    document.getElementById("detailTransaksiModal").style.display = "none";
    document.body.style.overflow = "";
}

function openFullHistoryModal() {
    renderFullHistoryTable();
    const fullHistoryModal = document.getElementById("fullHistoryModal");
    if (fullHistoryModal) fullHistoryModal.style.display = "block";
    document.body.style.overflow = "hidden";
}

function closeFullHistoryModal() {
    const fullHistoryModal = document.getElementById("fullHistoryModal");
    if (fullHistoryModal) fullHistoryModal.style.display = "none";
    document.body.style.overflow = "";
}

function loadChartByPeriod(period) {
    window.location.href = "/kDashboard?period=" + period;
}

function initEventListeners() {
    const lihatSemuaBtn = document.getElementById("lihatSemuaRiwayatBtn");
    if (lihatSemuaBtn) {
        lihatSemuaBtn.onclick = openFullHistoryModal;
    }
    const fullHistoryClose = document.querySelector(".full-history-close");
    if (fullHistoryClose) {
        fullHistoryClose.onclick = closeFullHistoryModal;
    }
    const modalCloseDetail = document.querySelector(".modal-close-detail");
    const closeDetailBtn = document.querySelector(".close-detail-btn");

    if (modalCloseDetail) {
        modalCloseDetail.onclick = closeDetailModal;
    }

    if (closeDetailBtn) {
        closeDetailBtn.onclick = closeDetailModal;
    }

    document.querySelectorAll(".period-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const period = this.dataset.period;
            loadChartByPeriod(period);
        });
    });

    // Logout
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function (e) {
            e.preventDefault();
            fetch("/logout", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            }).then(() => (window.location.href = "/login"));
        });
    }

    // Verifikasi buttons
    document.querySelectorAll(".verify-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            if (confirm("Verifikasi transaksi ini?")) {
                fetch(`/api/transaksi/${id}/approve`, {
                    method: "PUT",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                }).then(() => location.reload());
            }
        });
    });

    // Click outside modal
    window.onclick = function (e) {
        const fullHistoryModal = document.getElementById("fullHistoryModal");
        const detailModal = document.getElementById("detailTransaksiModal");

        if (e.target === fullHistoryModal) {
            closeFullHistoryModal();
        }
        if (e.target === detailModal) {
            closeDetailModal();
        }
    };
}
function init() {
    updatePeriodText();
    initMainChart();
    renderHistoryTable();
    initEventListeners();
}

document.addEventListener("DOMContentLoaded", init);
