// ============================================
// wTukarBotol.js - Upload sampah dan update poin ke database
// ============================================

let currentPoin = window.userData?.poin || 0;
let selectedImage = null;
let selectedImageBase64 = null;
let currentVolumeData = null;

// Data ukuran detail berdasarkan kategori (1 BOTOL)
const sizeDetails = {
    small: [
        { value: 220, botol: 1, berat: 0.22, poin: 15, name: "220 ML" },
        { value: 330, botol: 1, berat: 0.33, poin: 20, name: "330 ML" },
    ],
    medium: [
        { value: 450, botol: 1, berat: 0.45, poin: 25, name: "450 ML" },
        { value: 600, botol: 1, berat: 0.6, poin: 30, name: "600 ML" },
        { value: 750, botol: 1, berat: 0.75, poin: 35, name: "750 ML" },
    ],
    large: [
        { value: 1000, botol: 1, berat: 1.0, poin: 40, name: "1 L" },
        { value: 1500, botol: 1, berat: 1.5, poin: 50, name: "1.5 L" },
        { value: 2000, botol: 1, berat: 2.0, poin: 60, name: "2 L" },
    ],
};

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
        reader.onerror = (error) => reject(error);
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
    reader.onload = function (e) {
        const preview = document.getElementById("imagePreview");
        if (preview) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        }
    };
    reader.readAsDataURL(file);
    return true;
}

// Save transaction to database (1 BOTOL)
async function saveTransaction(jumlahBotol, beratBotol, poinDapat) {
    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        if (!csrfToken) {
            throw new Error("CSRF token tidak ditemukan");
        }

        const response = await fetch("/api/transaksi", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                jumlah_botol: jumlahBotol,
                berat_botol: beratBotol,
                poin_dapat: poinDapat,
            }),
        });

        if (!response.ok) {
            const text = await response.text();
            console.error("Response text:", text);
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error("Error save transaction:", error);
        return { success: false, message: "Gagal menyimpan: " + error.message };
    }
}

// Tampilkan pilihan ukuran detail berdasarkan kategori
function showDetailSizes(category) {
    const detailContainer = document.getElementById("detailSizeContainer");
    const detailButtonsDiv = document.getElementById("detailSizeButtons");

    if (!detailContainer || !detailButtonsDiv) return;

    const sizes = sizeDetails[category];
    if (!sizes || sizes.length === 0) return;

    detailButtonsDiv.innerHTML = "";
    sizes.forEach((size) => {
        const label = document.createElement("label");
        label.className = "detail-size-label";
        label.innerHTML = `
            <input type="radio" name="size_detail" value="${size.value}" 
                   data-botol="${size.botol}" data-berat="${size.berat}" 
                   data-poin="${size.poin}" data-size-name="${size.name}">
            ${size.value} ${size.value >= 1000 ? "L" : "ML"}
        `;
        detailButtonsDiv.appendChild(label);
    });

    detailContainer.style.display = "block";

    const sizeDetailRadios = document.querySelectorAll(
        'input[name="size_detail"]',
    );
    sizeDetailRadios.forEach((radio) => {
        radio.removeEventListener("change", handleSizeDetailChange);
        radio.addEventListener("change", handleSizeDetailChange);
    });
}

// Sembunyikan pilihan ukuran detail
function hideDetailSizes() {
    const detailContainer = document.getElementById("detailSizeContainer");
    if (detailContainer) {
        detailContainer.style.display = "none";
    }
    const selectedSizeDetail = document.querySelector(
        'input[name="size_detail"]:checked',
    );
    if (selectedSizeDetail) {
        selectedSizeDetail.checked = false;
    }
}

// Handle pemilihan ukuran detail
function handleSizeDetailChange(e) {
    const radio = e.target;
    if (radio.checked) {
        const botol = parseInt(radio.dataset.botol);
        const berat = parseFloat(radio.dataset.berat);
        const poin = parseInt(radio.dataset.poin);
        const sizeName = radio.dataset.sizeName;

        currentVolumeData = {
            sizeName: sizeName,
            botol: botol,
            berat: berat,
            poin: poin,
        };

        document.getElementById("summarySize").textContent =
            currentVolumeData.sizeName;
        document.getElementById("summaryBerat").textContent =
            currentVolumeData.berat;
        document.getElementById("summaryPoin").textContent =
            currentVolumeData.poin;
        document.getElementById("formSummary").style.display = "block";
    }
}

// Handle pemilihan kategori volume
function handleVolumeChange(e) {
    const radio = e.target;
    if (radio.checked) {
        const category = radio.dataset.category;

        showDetailSizes(category);

        currentVolumeData = null;
        document.getElementById("formSummary").style.display = "none";
        document.getElementById("summarySize").textContent = "-";
        document.getElementById("summaryBerat").textContent = "0";
        document.getElementById("summaryPoin").textContent = "0";

        const selectedSizeDetail = document.querySelector(
            'input[name="size_detail"]:checked',
        );
        if (selectedSizeDetail) {
            selectedSizeDetail.checked = false;
        }
    }
}

// Submit upload (1 BOTOL)
async function submitUpload(e) {
    e.preventDefault();

    if (!selectedImage) {
        showNotification(
            "❌ Silakan upload foto sampah plastik terlebih dahulu!",
            "error",
        );
        return;
    }
    if (!currentVolumeData) {
        showNotification("❌ Silakan pilih ukuran botol!", "error");
        return;
    }
    

    const submitBtn = document.querySelector(".submit-upload-btn");
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    submitBtn.disabled = true;

    try {
        const result = await saveTransaction(
            currentVolumeData.botol, // jumlah_botol = 1
            currentVolumeData.berat,
            currentVolumeData.poin,
        );

        if (result.success) {
            currentPoin = result.poin;
            updatePoinDisplay();

            showNotification(
                `✅ Berhasil! Anda mendapatkan ${currentVolumeData.poin} poin! 🌱`,
                "success",
            );

            await refreshDashboardStats();

            // Reset form
            selectedImage = null;
            selectedImageBase64 = null;
            currentVolumeData = null;
            document.getElementById("imagePreview").innerHTML = "";
            document.getElementById("formSummary").style.display = "none";
            document
                .querySelectorAll('input[name="volume"]')
                .forEach((radio) => (radio.checked = false));
            document
                .querySelectorAll('input[name="size_detail"]')
                .forEach((radio) => (radio.checked = false));
            document.getElementById("imageUpload").value = "";
            hideDetailSizes();

            const uploadArea = document.getElementById("uploadArea");
            if (uploadArea) uploadArea.style.display = "block";
        } else {
            showNotification(
                "❌ " + (result.message || "Gagal menyimpan transaksi!"),
                "error",
            );
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
        const response = await fetch("/api/stats");
        const data = await response.json();
        if (data.success) {
            localStorage.setItem("dashboardStats", JSON.stringify(data));
        }
    } catch (error) {
        console.error("Error refreshing stats:", error);
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

    volumeRadios.forEach((radio) => {
        radio.addEventListener("change", handleVolumeChange);
    });

    if (uploadForm) uploadForm.addEventListener("submit", submitUpload);
}

// Inisialisasi
function init() {
    updatePoinDisplay();
    initEventListeners();
}

document.addEventListener("DOMContentLoaded", init);
