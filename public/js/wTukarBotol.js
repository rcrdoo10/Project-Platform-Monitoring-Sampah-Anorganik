// ============================================
// wTukarBotol.js - Upload sampah dan update poin ke database
// ============================================

let currentPoin = window.userData?.poin || 0;
let selectedImage = null;
let selectedImageBase64 = null;
let currentVolumeData = null;

// Update tampilan poin
function updatePoinDisplay() {
    const poinDisplay = document.getElementById("poinTotalDisplay");
    if (poinDisplay) {
        poinDisplay.innerText = currentPoin.toLocaleString("id-ID");
    }

    const poinLevel = document.getElementById("poinLevel");
    if (poinLevel) {
        if (currentPoin >= 10000) {
            poinLevel.innerText = "Level: 🌟 Eco Master";
        } else if (currentPoin >= 5000) {
            poinLevel.innerText = "Level: 🌿 Eco Warrior";
        } else if (currentPoin >= 2000) {
            poinLevel.innerText = "Level: 💚 Pejuang Lingkungan";
        } else {
            poinLevel.innerText = "Level: 🌱 Pemula Hijau";
        }
    }
}

function showNotification(message, type = "success") {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas ${type === "success" ? "fa-check-circle" : type === "error" ? "fa-exclamation-circle" : "fa-info-circle"}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = "slideOutRight 0.3s ease";
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Convert image to base64
function getBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
    });
}

// Handle image upload
async function handleImageUpload(file) {
    if (!file || !file.type.startsWith("image/")) {
        showNotification("Silakan upload file gambar yang valid!", "error");
        return false;
    }
    if (file.size > 5 * 1024 * 1024) {
        showNotification("Ukuran gambar maksimal 5MB!", "error");
        return false;
    }

    selectedImage = file;
    selectedImageBase64 = await getBase64(file);
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById("imagePreview");
        if (preview) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        }
    };
    reader.readAsDataURL(file);
    return true;
}

// Simpan transaksi ke database (LENGKAP dengan image)
async function saveTransaction(jumlahBotol, beratBotol, poinDapat) {
    try {
        const response = await fetch("/api/save-transaksi", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
            },
            body: JSON.stringify({
                jumlah_botol: jumlahBotol,
                berat_botol: beratBotol,
                poin_dapat: poinDapat,
                status_scan: "success",
                image: selectedImageBase64
            }),
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.error("Error save transaction:", error);
        return { success: false, message: "Network error" };
    }
}

// Update summary
function updateSummary() {
    const selected = document.querySelector('input[name="volume"]:checked');
    if (selected) {
        currentVolumeData = {
            volume: selected.value,
            botol: parseInt(selected.dataset.botol),
            berat: parseFloat(selected.dataset.berat),
            poin: parseInt(selected.dataset.poin),
        };
        document.getElementById("summaryBotol").textContent = currentVolumeData.botol;
        document.getElementById("summaryBerat").textContent = currentVolumeData.berat;
        document.getElementById("summaryPoin").textContent = currentVolumeData.poin;
        document.getElementById("formSummary").style.display = "block";
    } else {
        document.getElementById("formSummary").style.display = "none";
        currentVolumeData = null;
    }
}

// Submit upload
async function submitUpload(e) {
    e.preventDefault();

    if (!selectedImage) {
        showNotification("❌ Silakan upload foto sampah plastik terlebih dahulu!", "error");
        return;
    }
    if (!currentVolumeData) {
        showNotification("❌ Silakan pilih volume botol!", "error");
        return;
    }

    const submitBtn = document.querySelector(".submit-upload-btn");
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    submitBtn.disabled = true;

    try {
        // Simpan transaksi ke database (data_sampah + akun_sampah + update poin)
        const result = await saveTransaction(
            currentVolumeData.botol,
            currentVolumeData.berat,
            currentVolumeData.poin
        );

        if (result.success) {
            currentPoin = result.poin;
            updatePoinDisplay();
            
            showNotification(`✅ Berhasil! Anda mendapatkan ${currentVolumeData.poin} poin! 🌱`, "success");
            
            // Refresh dashboard stats
            await refreshDashboardStats();
            
            // Reset form
            selectedImage = null;
            selectedImageBase64 = null;
            currentVolumeData = null;
            document.getElementById("imagePreview").innerHTML = "";
            document.getElementById("formSummary").style.display = "none";
            document.querySelectorAll('input[name="volume"]').forEach(radio => radio.checked = false);
            document.getElementById("imageUpload").value = "";
            
            // Reset upload area
            const uploadArea = document.getElementById("uploadArea");
            if (uploadArea) uploadArea.style.display = "block";
        } else {
            showNotification("❌ " + (result.message || "Gagal menyimpan transaksi!"), "error");
        }
    } catch (error) {
        console.error("Error:", error);
        showNotification("❌ Terjadi kesalahan, silakan coba lagi!", "error");
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Refresh dashboard stats
async function refreshDashboardStats() {
    try {
        const response = await fetch('/api/get-all-stats');
        const data = await response.json();
        if (data.success) {
            localStorage.setItem('dashboardStats', JSON.stringify(data));
        }
    } catch (error) {
        console.error('Error refreshing stats:', error);
    }
}

// Toggle navbar
function toggleNav() {
    const navToggle = document.getElementById("navToggle");
    const navigation = document.getElementById("navigation");
    const overlay = document.getElementById("overlay");

    if (navToggle) navToggle.classList.toggle("active");
    if (navigation) navigation.classList.toggle("active");
    if (overlay) overlay.classList.toggle("active");
}

// Inisialisasi event listeners
function initEventListeners() {
    const navToggle = document.getElementById("navToggle");
    const overlay = document.getElementById("overlay");

    if (navToggle) navToggle.addEventListener("click", toggleNav);
    if (overlay) overlay.addEventListener("click", toggleNav);

    const uploadArea = document.getElementById("uploadArea");
    const imageInput = document.getElementById("imageUpload");
    const volumeRadios = document.querySelectorAll('input[name="volume"]');
    const uploadForm = document.getElementById("uploadForm");

    if (uploadArea && imageInput) {
        uploadArea.addEventListener("click", () => imageInput.click());

        uploadArea.addEventListener("dragover", (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = "#1e5a48";
            uploadArea.style.background = "#e8f5f1";
        });

        uploadArea.addEventListener("dragleave", () => {
            uploadArea.style.borderColor = "#2e7d64";
            uploadArea.style.background = "#f0f4f8";
        });

        uploadArea.addEventListener("drop", async (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = "#2e7d64";
            uploadArea.style.background = "#f0f4f8";
            if (e.dataTransfer.files[0]) {
                await handleImageUpload(e.dataTransfer.files[0]);
            }
        });
    }

    if (imageInput) {
        imageInput.addEventListener("change", async (e) => {
            if (e.target.files[0]) {
                await handleImageUpload(e.target.files[0]);
            }
        });
    }

    volumeRadios.forEach(radio => radio.addEventListener("change", updateSummary));
    if (uploadForm) uploadForm.addEventListener("submit", submitUpload);
}

// Inisialisasi
function init() {
    updatePoinDisplay();
    initEventListeners();
}

document.addEventListener("DOMContentLoaded", init);