// Ambil elemen
const form = document.getElementById('loginForm');
const forgotLink = document.getElementById('forgotLink');
const fbLink = document.getElementById('fb');
const igLink = document.getElementById('ig');
const ytLink = document.getElementById('yt');
const toast = document.getElementById('toast');

// Fungsi show toast
function showToast(msg, isError = true) {
    toast.textContent = msg;
    toast.classList.remove('hidden');
    toast.style.background = isError ? '#e74c3c' : '#27ae60';
    
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 2000);
}

// Login
form.addEventListener('submit', (e) => {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    
    if (!username || !password) {
        showToast('Username dan password harus diisi!');
        return;
    }
    
    // Demo login: username "admin" password "admin"
    if (username === 'admin' && password === 'admin') {
        showToast('Login berhasil! Selamat datang.', false);
    } else {
        showToast('Login gagal! Coba username: admin, password: admin');
    }
});

// Forgot password
forgotLink.addEventListener('click', (e) => {
    e.preventDefault();
    showToast('Silakan hubungi admin untuk reset password.', false);
});

// Social media links
fbLink.addEventListener('click', (e) => {
    e.preventDefault();
    window.open('https://facebook.com', '_blank');
});

igLink.addEventListener('click', (e) => {
    e.preventDefault();
    window.open('https://instagram.com', '_blank');
});

ytLink.addEventListener('click', (e) => {
    e.preventDefault();
    window.open('https://youtube.com', '_blank');
});


