let totalPoin = 9999;
let selectedImage = null;
let currentVolumeData = null;

let labels = ["1 Mei", "8 Mei", "15 Mei", "22 Mei", "29 Mei"];
let poinData = [120, 145, 168, 192, 220];
let botolData = [100, 118, 134, 155, 170];
let beratData = [78, 92, 105, 122, 130];
let allHistoryData = [];

function loadData() {
    const savedPoin = localStorage.getItem("totalPoin");
    const savedHistory = localStorage.getItem("allHistoryData");
    const savedPoinData = localStorage.getItem("poinData");
    const savedBotolData = localStorage.getItem("botolData");
    const savedBeratData = localStorage.getItem("beratData");
    const savedLabels = localStorage.getItem("labels");

    if (savedPoin) totalPoin = parseInt(savedPoin);
    if (savedHistory) allHistoryData = JSON.parse(savedHistory);
    if (savedPoinData) poinData = JSON.parse(savedPoinData);
    if (savedBotolData) botolData = JSON.parse(savedBotolData);
    if (savedBeratData) beratData = JSON.parse(savedBeratData);
    if (savedLabels) labels = JSON.parse(savedLabels);

    updatePoinDisplay();
}

function saveData() {
    localStorage.setItem("totalPoin", totalPoin);
    localStorage.setItem("allHistoryData", JSON.stringify(allHistoryData));
    localStorage.setItem("poinData", JSON.stringify(poinData));
    localStorage.setItem("botolData", JSON.stringify(botolData));
    localStorage.setItem("beratData", JSON.stringify(beratData));
    localStorage.setItem("labels", JSON.stringify(labels));
}

function updatePoinDisplay() {
    const poinDisplay = document.getElementById("poinTotalDisplay");
    if (poinDisplay) {
        poinDisplay.innerText = totalPoin.toLocaleString("id-ID");
    }

    const poinLevel = document.getElementById("poinLevel");
    if (poinLevel) {
        if (totalPoin >= 10000) {
            poinLevel.innerText = "Level: 🌟 Eco Master";
        } else if (totalPoin >= 5000) {
            poinLevel.innerText = "Level: 🌿 Eco Warrior";
        } else if (totalPoin >= 2000) {
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

function handleImageUpload(file) {
    if (!file || !file.type.startsWith("image/")) {
        showNotification("Silakan upload file gambar yang valid!", "error");
        return false;
    }
    if (file.size > 5 * 1024 * 1024) {
        showNotification("Ukuran gambar maksimal 5MB!", "error");
        return false;
    }
    
    selectedImage = file;
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

function submitUpload(e) {
    e.preventDefault();
    
    if (!selectedImage) {
        showNotification("❌ Silakan upload foto sampah plastik terlebih dahulu!", "error");
        return;
    }
    if (!currentVolumeData) {
        showNotification("❌ Silakan pilih volume botol!", "error");
        return;
    }

    const now = new Date();
    const tanggalString = now.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    }) + ", " + now.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });

    allHistoryData.unshift({
        tanggal: tanggalString,
        botol: `${currentVolumeData.botol} Botol`,
        berat: `${currentVolumeData.berat.toFixed(2)} kg`,
        poin: `+${currentVolumeData.poin}`,
        status: "Berhasil",
    });

    totalPoin += currentVolumeData.poin;

    const lastDate = labels[labels.length - 1];
    const nextWeek = parseInt(lastDate.split(" ")[0]) + 7;
    labels.push(`${nextWeek} Mei`);
    poinData.push(poinData[poinData.length - 1] + currentVolumeData.poin);
    botolData.push(botolData[botolData.length - 1] + currentVolumeData.botol);
    beratData.push(beratData[beratData.length - 1] + currentVolumeData.berat);

    saveData();
    updatePoinDisplay();

    selectedImage = null;
    currentVolumeData = null;
    document.getElementById("imagePreview").innerHTML = "";
    document.getElementById("formSummary").style.display = "none";
    document.querySelectorAll('input[name="volume"]').forEach((radio) => radio.checked = false);
    document.getElementById("imageUpload").value = "";

    showNotification(`✅ Berhasil! Anda mendapatkan ${currentVolumeData.poin} poin! 🌱`, "success");
}

function toggleNav() {
    const navToggle = document.getElementById("navToggle");
    const navigation = document.getElementById("navigation");
    const overlay = document.getElementById("overlay");
    
    if (navToggle) navToggle.classList.toggle("active");
    if (navigation) navigation.classList.toggle("active");
    if (overlay) overlay.classList.toggle("active");
}

function initEventListeners() {
    const navToggle = document.getElementById("navToggle");
    const overlay = document.getElementById("overlay");
    
    if (navToggle) navToggle.addEventListener("click", toggleNav);
    if (overlay) overlay.addEventListener("click", toggleNav);

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            showNotification("🚪 Anda telah logout dari Dompet Sadhar. Sampai jumpa kembali! 🌱", "info");
        });
    }

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
        
        uploadArea.addEventListener("dragleave", (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = "#2e7d64";
            uploadArea.style.background = "#f0f4f8";
        });
        
        uploadArea.addEventListener("drop", (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = "#2e7d64";
            uploadArea.style.background = "#f0f4f8";
            if (e.dataTransfer.files[0]) handleImageUpload(e.dataTransfer.files[0]);
        });
    }

    if (imageInput) {
        imageInput.addEventListener("change", (e) => {
            if (e.target.files[0]) handleImageUpload(e.target.files[0]);
        });
    }

    volumeRadios.forEach((radio) => radio.addEventListener("change", updateSummary));
    
    if (uploadForm) uploadForm.addEventListener("submit", submitUpload);
}

function init() {
    loadData();
    initEventListeners();
}

document.addEventListener("DOMContentLoaded", init);