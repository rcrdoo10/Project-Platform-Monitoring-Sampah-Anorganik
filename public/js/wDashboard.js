// ----- DATA DASHBOARD -----
const labels = ['1 Mei', '8 Mei', '15 Mei', '22 Mei', '29 Mei'];
const poinData = [120, 145, 168, 192, 220];
const botolData = [100, 118, 134, 155, 170];
const beratData = [78, 92, 105, 122, 130];

// Data riwayat
const historyData = [
    { tanggal: "1 Mei 2026, 10:30", botol: "100 Botol", berat: "1,5 kg", poin: "+150", status: "Berhasil" },
    { tanggal: "3 Mei 2026, 14:15", botol: "45 Botol", berat: "0,7 kg", poin: "+67", status: "Berhasil" },
    { tanggal: "8 Mei 2026, 09:45", botol: "120 Botol", berat: "1,8 kg", poin: "+180", status: "Berhasil" },
    { tanggal: "15 Mei 2026, 11:20", botol: "80 Botol", berat: "1,2 kg", poin: "+120", status: "Berhasil" },
    { tanggal: "22 Mei 2026, 16:00", botol: "150 Botol", berat: "2,3 kg", poin: "+225", status: "Berhasil" }
];

// Nilai total
let totalPoin = 9999;
let totalBotol = 9999;
let totalBerat = 9999;

let mainChart;

// Render tabel riwayat
function renderHistoryTable() {
    const tbody = document.getElementById('historyBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    historyData.forEach(item => {
        const row = tbody.insertRow();
        row.insertCell(0).innerText = item.tanggal;
        row.insertCell(1).innerText = item.botol;
        row.insertCell(2).innerText = item.berat;
        row.insertCell(3).innerHTML = `<strong style="color:#2b7a5c;">${item.poin}</strong>`;
        row.insertCell(4).innerHTML = `<span class="status-badge"><i class="fas fa-check-circle"></i> ${item.status}</span>`;
        row.insertCell(5).innerHTML = `<a href="#" class="detail-link" data-tgl="${item.tanggal}">Lihat detail</a>`;
    });
    
    document.querySelectorAll('.detail-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const tgl = link.getAttribute('data-tgl') || 'transaksi';
            alert(`✨ Detail transaksi: ${tgl}\nJumlah botol dan poin sudah tercatat. Terima kasih telah berkontribusi!`);
        });
    });
}

// Inisialisasi grafik
function initMainChart() {
    const ctx = document.getElementById('wasteChart').getContext('2d');
    if (mainChart) mainChart.destroy();
    mainChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Poin',
                    data: poinData,
                    borderColor: '#2c7da0',
                    backgroundColor: 'rgba(44,125,160,0.03)',
                    borderWidth: 3,
                    tension: 0.2,
                    fill: false,
                    pointBackgroundColor: '#2c7da0',
                    pointBorderColor: '#ffffff',
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                {
                    label: 'Botol',
                    data: botolData,
                    borderColor: '#52b788',
                    backgroundColor: 'transparent',
                    borderWidth: 3,
                    tension: 0.2,
                    fill: false,
                    pointBackgroundColor: '#52b788',
                    pointBorderColor: '#ffffff',
                    pointRadius: 5
                },
                {
                    label: 'Berat (kg)',
                    data: beratData,
                    borderColor: '#e9c46a',
                    backgroundColor: 'transparent',
                    borderWidth: 3,
                    tension: 0.2,
                    fill: false,
                    pointBackgroundColor: '#e9c46a',
                    pointBorderColor: '#ffffff',
                    pointRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                tooltip: { mode: 'index', intersect: false },
                legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 12, font: { size: 12 } } }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#eef2f5' },
                    title: { display: true, text: 'Jumlah / Poin / Kg', color: '#5e7c8c' }
                },
                x: { 
                    grid: { display: false },
                    title: { display: true, text: 'Tanggal (Mei 2026)', color: '#5e7c8c' }
                }
            }
        }
    });
}

// Update angka statistik
function updateDashboardNumbers() {
    document.getElementById('totalPoinVal').innerText = totalPoin.toLocaleString('id-ID');
    document.getElementById('totalBotolVal').innerText = totalBotol.toLocaleString('id-ID');
    document.getElementById('totalBeratVal').innerText = totalBerat.toLocaleString('id-ID');
}

// Navigasi
function setActivePage(pageId) {
    const navItems = document.querySelectorAll('.navigation ul li');
    navItems.forEach(item => {
        item.classList.remove('active');
        if(item.getAttribute('data-page') === pageId) {
            item.classList.add('active');
        }
    });
    
    if(pageId === 'verifikasi') {
        alert("📋 Halaman Verifikasi: Anda dapat memindai QR atau unggah bukti setoran botol plastik. Fitur segera hadir.");
    } else if(pageId === 'reward') {
        alert("🎁 Halaman Reward: Tukarkan poinmu dengan berbagai hadiah menarik. Total poin anda: " + totalPoin.toLocaleString());
    }
    
    if(navigation.classList.contains('active')) toggleNav();
}

// Toggle navigasi
const navToggle = document.getElementById('navToggle');
const navigation = document.getElementById('navigation');
const overlay = document.getElementById('overlay');

function toggleNav() {
    navToggle.classList.toggle('active');
    navigation.classList.toggle('active');
    overlay.classList.toggle('active');
}

// Event listeners
function initEventListeners() {
    navToggle.addEventListener('click', toggleNav);
    overlay.addEventListener('click', toggleNav);
    
    const menuItems = document.querySelectorAll('.navigation ul li');
    menuItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const page = item.getAttribute('data-page');
            if(page) setActivePage(page);
        });
    });
    
    const logoutBtn = document.getElementById('logoutSideBtn');
    if(logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            alert("🚪 Anda telah logout dari Dompet Sadhar. Sampai jumpa kembali! 🌱");
        });
    }
}

// Set default active menu
function setDefaultActive() {
    const defaultNav = document.querySelector('.navigation ul li[data-page="dashboard"]');
    if(defaultNav) defaultNav.classList.add('active');
}

// Inisialisasi semua
function init() {
    renderHistoryTable();
    initMainChart();
    updateDashboardNumbers();
    initEventListeners();
    setDefaultActive();
    
    window.addEventListener('resize', () => {
        if(mainChart) mainChart.resize();
    });
}

// Jalankan saat DOM siap
document.addEventListener('DOMContentLoaded', init);