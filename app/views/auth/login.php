<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
<title>Login — Inspeksi Kebersihan Al-Wildan Islamic School 8 Kemang Bogor</title>

<!-- PWA Support -->
<link rel="manifest" href="<?= Helper::baseUrl('manifest.json') ?>">
<meta name="theme-color" content="#0f2744">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Inspeksi">
<link rel="apple-touch-icon" sizes="180x180" href="<?= Helper::baseUrl('icons/apple-touch-icon-180x180.png') ?>">
<link rel="apple-touch-icon" href="<?= Helper::baseUrl('icons/apple-touch-icon.png') ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?= Helper::baseUrl('icons/icon-192x192.png') ?>">

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
  --gold:        #f59e0b;
}
* { box-sizing: border-box; }
body {
  min-height: 100vh;
  margin: 0;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  background: linear-gradient(135deg, #0a192f 0%, #0f2744 45%, #1a56db 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

/* Ornament background */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image:
    radial-gradient(circle at 15% 20%, rgba(56,189,248,.15) 0%, transparent 40%),
    radial-gradient(circle at 85% 80%, rgba(26,86,219,.25) 0%, transparent 45%);
  pointer-events: none;
}

.login-wrap {
  width: 100%;
  max-width: 420px;
  position: relative;
  z-index: 1;
}

/* Card */
.login-card {
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(10,25,47,.45);
}

/* Header */
.login-header {
  background: linear-gradient(135deg, #0a192f 0%, #0f2744 60%, #1a56db 100%);
  padding: 32px 32px 26px;
  text-align: center;
  position: relative;
}
.login-header::after {
  content: '';
  position: absolute;
  bottom: -1px; left: 0; right: 0;
  height: 24px;
  background: #fff;
  border-radius: 20px 20px 0 0;
}
.logo-box {
  width: 80px; height: 80px;
  background: #ffffff;
  border: 3px solid rgba(255,255,255,.9);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 14px;
  padding: 8px;
  box-shadow: 0 6px 18px rgba(0,0,0,.25);
}
.logo-box img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.login-header h1 {
  font-size: 19px;
  font-weight: 700;
  color: #fff;
  margin: 0 0 4px;
}
.login-header p {
  font-size: 13px;
  color: rgba(224,242,254,.85);
  margin: 0;
}

/* Body */
.login-body {
  padding: 24px 32px 32px;
}

.arabic-quote {
  text-align: center;
  font-size: 15px;
  color: var(--blue-navy);
  font-style: italic;
  margin-bottom: 22px;
  padding: 12px 16px;
  background: var(--blue-light);
  border-radius: 10px;
  border-left: 3px solid var(--blue-sky);
}
.arabic-quote small {
  display: block;
  font-size: 11px;
  color: #64748b;
  font-style: normal;
  margin-top: 2px;
}

.form-label {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
  margin-bottom: 6px;
}
.form-control {
  border-radius: 10px;
  border: 1.5px solid #cbd5e1;
  padding: 10px 14px;
  font-size: 14px;
  transition: border-color .2s, box-shadow .2s;
}
.form-control:focus {
  border-color: var(--blue-main);
  box-shadow: 0 0 0 3px rgba(26,86,219,.15);
  outline: none;
}
.input-group .form-control { border-radius: 10px 0 0 10px; }
.input-group .btn-outline-secondary {
  border-radius: 0 10px 10px 0;
  border: 1.5px solid #cbd5e1;
  border-left: none;
  color: #64748b;
}
.input-group .btn-outline-secondary:hover { background: var(--blue-light); color: var(--blue-navy); }

.btn-login {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  background: linear-gradient(135deg, var(--blue-navy), var(--blue-main));
  border: none;
  color: #fff;
  font-size: 15px;
  font-weight: 600;
  letter-spacing: .3px;
  transition: opacity .2s, transform .1s, box-shadow .2s;
  margin-top: 8px;
  box-shadow: 0 4px 14px rgba(26,86,219,.3);
}
.btn-login:hover  { opacity: .95; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(26,86,219,.4); }
.btn-login:active { transform: translateY(0); }
.btn-login:disabled { opacity: .7; }

.alert { border-radius: 10px; font-size: 13px; padding: 10px 14px; }

.footer-note {
  text-align: center;
  font-size: 12px;
  color: rgba(255,255,255,.65);
  margin-top: 18px;
}
</style>
</head>
<body>

<div class="login-wrap">
  <div class="login-card">

    <div class="login-header">
      <div class="logo-box">
        <img src="<?= Helper::baseUrl('img/logo-alwildan.png') ?>" alt="Logo Al Wildan">
      </div>
      <h1>Inspeksi Kebersihan</h1>
      <p style="font-weight:600; color:#1e293b; margin-bottom:2px;">Al-Wildan Islamic School 8 Kemang Bogor</p>
      <small style="color:#2563eb; font-size:11.5px; font-weight:500;">Developer: Yayasan Ambadar</small>
    </div>

    <div class="login-body">

      <?php if ($timeout ?? false): ?>
        <div class="alert alert-warning">
          <i class="bi bi-clock me-1"></i> Sesi Anda habis. Silakan login kembali.
        </div>
      <?php endif; ?>

      <?= Helper::flashAlert() ?>

      <div class="arabic-quote">
        الطَّهُورُ شَطْرُ الْإِيمَانِ
        <small>Kebersihan adalah sebagian dari iman. (HR. Muslim)</small>
      </div>

      <form method="POST" action="<?= Helper::baseUrl('login') ?>" id="loginForm" novalidate>
        <?= Helper::csrfField() ?>

        <div class="mb-3">
          <label class="form-label" for="email">
            <i class="bi bi-envelope me-1"></i>Email
          </label>
          <input
            type="email"
            class="form-control"
            id="email"
            name="email"
            placeholder="admin@alwildan.sch.id"
            value="<?= Helper::e($_POST['email'] ?? '') ?>"
            autocomplete="email"
            required
          >
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">
            <i class="bi bi-lock me-1"></i>Password
          </label>
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
          <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
        </button>
      </form>

    </div><!-- /login-body -->
  </div><!-- /login-card -->

  <p class="footer-note">
    &copy; <?= date('Y') ?> Al-Wildan Islamic School 8 Kemang Bogor<br>
    <span style="opacity:0.85">Sistem Inspeksi Kebersihan &bull; Dikembangkan oleh Yayasan Ambadar</span>
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

// Disable submit button on submit (prevent double post)
document.getElementById('loginForm').addEventListener('submit', function() {
  const btn = document.getElementById('btnLogin');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Memproses...';
});

// Auto focus
document.getElementById('email').focus();

// PWA Service Worker Registration
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('<?= Helper::baseUrl("sw.js") ?>')
      .then(reg => console.log('SW Registered for Al Wildan:', reg.scope))
      .catch(err => console.log('SW Registration failed:', err));
  });
}
</script>
</body>
</html>
