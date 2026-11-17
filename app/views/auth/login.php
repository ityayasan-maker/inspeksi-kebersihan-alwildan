<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
<title>Login — Inspeksi Kebersihan Al-Wildan Islamic School 8 Kemang Bogor</title>

<!-- PWA Support -->
<link rel="manifest" href="<?= Helper::baseUrl('manifest.json') ?>">
<meta name="theme-color" content="#0a192f">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Inspeksi">
<link rel="apple-touch-icon" sizes="180x180" href="<?= Helper::baseUrl('icons/apple-touch-icon-180x180.png') ?>">
<link rel="apple-touch-icon" href="<?= Helper::baseUrl('icons/apple-touch-icon.png') ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?= Helper::baseUrl('icons/icon-192x192.png') ?>">
<link rel="icon" type="image/png" sizes="512x512" href="<?= Helper::baseUrl('icons/icon-512x512.png') ?>">

<!-- Google Fonts: Poppins -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
:root {
  --blue-dark:   #0a192f;
  --blue-navy:   #0f2744;
  --blue-main:   #1a56db;
  --blue-royal:  #2563eb;
  --blue-sky:    #0284c7;
  --blue-light:  #f0f7ff;
  --blue-mid:    #dbeafe;
}
* {
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif !important;
}
body {
  min-height: 100vh;
  margin: 0;
  background: linear-gradient(135deg, #0a192f 0%, #0f2744 45%, #1a56db 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
  position: relative;
}

/* Background glows */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image:
    radial-gradient(circle at 15% 20%, rgba(56,189,248,.12) 0%, transparent 40%),
    radial-gradient(circle at 85% 80%, rgba(26,86,219,.22) 0%, transparent 45%);
  pointer-events: none;
}

/* Floating Banner Top (Mobile Install Notification) */
.pwa-top-toast {
  position: fixed;
  top: 12px;
  left: 50%;
  transform: translateX(-50%);
  width: 92%;
  max-width: 440px;
  background: #ffffff;
  color: #0f2744;
  border-radius: 14px;
  padding: 10px 14px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.35);
  z-index: 99999;
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid rgba(26,86,219,0.25);
  animation: slideDown 0.4s ease-out;
}
@keyframes slideDown {
  from { top: -80px; opacity: 0; }
  to { top: 12px; opacity: 1; }
}
.pwa-top-toast .toast-logo {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #ffffff;
  padding: 4px;
  border: 1px solid #e2e8f0;
  flex-shrink: 0;
}
.pwa-top-toast .toast-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.pwa-top-toast .toast-content {
  flex-grow: 1;
}
.pwa-top-toast .toast-content strong {
  display: block;
  font-size: 12.5px;
  font-weight: 600;
  color: #0f2744;
  line-height: 1.2;
}
.pwa-top-toast .toast-content span {
  font-size: 11px;
  color: #64748b;
  display: block;
}
.pwa-top-toast .btn-install {
  background: #1a56db;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  padding: 6px 12px;
  cursor: pointer;
  white-space: nowrap;
}
.pwa-top-toast .btn-close-toast {
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 16px;
  padding: 2px 4px;
  cursor: pointer;
}

.login-wrap {
  width: 100%;
  max-width: 420px;
  position: relative;
  z-index: 1;
  margin-top: 10px;
}

/* Card */
.login-card {
  background: #fff;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(10,25,47,.55);
}

/* Header */
.login-header {
  background: linear-gradient(135deg, #0a192f 0%, #0f2744 60%, #1a56db 100%);
  padding: 34px 28px 24px;
  text-align: center;
  position: relative;
}
.login-header::after {
  content: '';
  position: absolute;
  bottom: -1px; left: 0; right: 0;
  height: 22px;
  background: #fff;
  border-radius: 20px 20px 0 0;
}

.logo-box {
  width: 86px;
  height: 86px;
  background: #ffffff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  padding: 8px;
  box-shadow: 0 8px 24px rgba(0,0,0,.3);
  animation: logoFade 0.6s ease-out;
}
@keyframes logoFade {
  0% { transform: scale(0.85); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}
.logo-box img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.login-header h1 {
  font-size: 20px;
  font-weight: 700;
  color: #ffffff !important;
  margin: 0 0 4px;
  letter-spacing: -0.2px;
}
.login-header p {
  font-size: 13.5px;
  font-weight: 500;
  color: #ffffff !important;
  opacity: 0.95;
  margin: 0;
  line-height: 1.35;
}

/* Body */
.login-body {
  padding: 24px 32px 32px;
}

.form-label {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 6px;
}
.form-control {
  border-radius: 12px;
  border: 1.5px solid #cbd5e1;
  padding: 11px 16px;
  font-size: 14px;
  transition: border-color .2s, box-shadow .2s;
  color: #0f2744;
}
.form-control:focus {
  border-color: #1a56db;
  box-shadow: 0 0 0 3px rgba(26,86,219,.15);
  outline: none;
}
.form-control::placeholder {
  color: #94a3b8;
  font-weight: 400;
}

.input-group .form-control { border-radius: 12px 0 0 12px; }
.input-group .btn-outline-secondary {
  border-radius: 0 12px 12px 0;
  border: 1.5px solid #cbd5e1;
  border-left: none;
  color: #64748b;
  padding: 0 16px;
}
.input-group .btn-outline-secondary:hover {
  background: #f0f7ff;
  color: #1a56db;
}

.btn-login {
  width: 100%;
  padding: 13px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0a192f, #1a56db);
  border: none;
  color: #fff;
  font-size: 15px;
  font-weight: 600;
  letter-spacing: .3px;
  transition: all .2s ease;
  margin-top: 10px;
  box-shadow: 0 6px 18px rgba(26,86,219,.35);
}
.btn-login:hover {
  opacity: .95;
  transform: translateY(-1px);
  box-shadow: 0 8px 22px rgba(26,86,219,.45);
}
.btn-login:active {
  transform: translateY(0);
}
.btn-login:disabled {
  opacity: .7;
}

.alert {
  border-radius: 12px;
  font-size: 13px;
  padding: 10px 14px;
}

.footer-note {
  text-align: center;
  font-size: 12px;
  color: rgba(255,255,255,.8);
  margin-top: 18px;
  font-weight: 400;
}
</style>
</head>
<body>

<!-- Floating PWA Install Notification Bar (Atas Web) -->
<div id="pwaTopToast" class="pwa-top-toast d-none">
  <div class="toast-logo">
    <img src="<?= Helper::baseUrl('icons/icon-192x192.png') ?>" alt="Logo">
  </div>
  <div class="toast-content">
    <strong>Aplikasi Inspeksi Al-Wildan</strong>
    <span id="toastSubtext">Pasang di HP agar akses lebih cepat & praktis</span>
  </div>
  <button type="button" class="btn-install" id="btnTopInstall">Pasang</button>
  <button type="button" class="btn-close-toast" id="btnCloseToast">✕</button>
</div>

<div class="login-wrap">
  <div class="login-card">

    <div class="login-header">
      <div class="logo-box">
        <img src="<?= Helper::baseUrl('img/logo-alwildan.png') ?>" alt="Logo Al Wildan">
      </div>
      <h1>Inspeksi Kebersihan</h1>
      <p>Al-Wildan Islamic School 8 Kemang Bogor</p>
    </div>

    <div class="login-body">

      <?php if ($timeout ?? false): ?>
        <div class="alert alert-warning">
          Sesi Anda habis. Silakan login kembali.
        </div>
      <?php endif; ?>

      <?= Helper::flashAlert() ?>

      <form method="POST" action="<?= Helper::baseUrl('login') ?>" id="loginForm" novalidate>
        <?= Helper::csrfField() ?>

        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input
            type="email"
            class="form-control"
            id="email"
            name="email"
            placeholder="Masukkan email"
            value="<?= Helper::e($_POST['email'] ?? '') ?>"
            autocomplete="email"
            autocorrect="off"
            autocapitalize="none"
            spellcheck="false"
            required
          >
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <div class="input-group">
            <input
              type="password"
              class="form-control"
              id="password"
              name="password"
              placeholder="Masukkan password"
              autocomplete="current-password"
              required
            >
            <button type="button" class="btn btn-outline-secondary" id="togglePw" tabindex="-1">
              <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-login" id="btnLogin">
          Masuk
        </button>
      </form>

    </div><!-- /login-body -->
  </div><!-- /login-card -->

  <p class="footer-note">
    &copy; <?= date('Y') ?> Al-Wildan Islamic School 8 Kemang Bogor
  </p>
</div>

<script>
// Toggle show/hide password
const togglePw = document.getElementById('togglePw');
const pwInput  = document.getElementById('password');
const eyeIcon  = document.getElementById('eyeIcon');

togglePw.addEventListener('click', () => {
  const isPw = pwInput.type === 'password';
  pwInput.type = isPw ? 'text' : 'password';
  eyeIcon.className = isPw ? 'bi bi-eye-slash' : 'bi bi-eye';
});

// Prevent double post & clean input
document.getElementById('loginForm').addEventListener('submit', function() {
  const emailInput = document.getElementById('email');
  if (emailInput) {
    emailInput.value = emailInput.value.trim().replace(/[\s\u00A0\u200B\uFEFF]+/g, '');
  }
  const btn = document.getElementById('btnLogin');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
});

document.getElementById('email').focus();

// ── PWA Install Floating Banner (Android & iOS) ──
let deferredPrompt = null;
const pwaToast     = document.getElementById('pwaTopToast');
const btnInstall   = document.getElementById('btnTopInstall');
const btnClose     = document.getElementById('btnCloseToast');
const toastSubtext = document.getElementById('toastSubtext');

const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
const isStandalone = window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches;

// Tutup banner
btnClose.addEventListener('click', () => {
  pwaToast.classList.add('d-none');
  sessionStorage.setItem('toast_dismissed', '1');
});

// Android / Chrome PWA install prompt
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  if (!isStandalone && !sessionStorage.getItem('toast_dismissed')) {
    pwaToast.classList.remove('d-none');
  }
});

btnInstall.addEventListener('click', () => {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(() => {
      deferredPrompt = null;
      pwaToast.classList.add('d-none');
    });
  } else if (isIos) {
    alert("Cara Pasang di iPhone/iPad:\n1. Ketuk tombol 'Bagikan' (kotak dengan panah ke atas) di bawah Safari.\n2. Gulir ke bawah lalu pilih 'Tambah ke Layar Utama' (+).");
  } else {
    alert("Untuk memasang aplikasi:\nBuka menu browser Anda (titik tiga di kanan atas) lalu pilih 'Pasang Aplikasi' atau 'Tambahkan ke Layar Utama'.");
  }
});

// Khusus iOS Safari jika belum pasang
if (isIos && !isStandalone && !sessionStorage.getItem('toast_dismissed')) {
  window.addEventListener('load', () => {
    toastSubtext.textContent = "Ketuk Pasang untuk panduan iPhone";
    pwaToast.classList.remove('d-none');
  });
}

// PWA Service Worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('<?= Helper::baseUrl("sw.js") ?>')
      .catch(err => console.log('SW Registration error:', err));
  });
}
</script>
</body>
</html>
