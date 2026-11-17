<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
<title><?= Helper::e($pageTitle ?? 'Dashboard') ?> — Inspeksi Kebersihan</title>

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

<!-- Google Fonts: Poppins -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<style>
* { font-family: 'Poppins', sans-serif !important; }
:root {
  --blue-dark:   #0a192f;
  --blue-navy:   #0f2744;
  --blue-main:   #1a56db;
  --blue-royal:  #2563eb;
  --blue-light:  #f0f7ff;
  --green-dark:  #0f2744;
  --green-main:  #1a56db;
  --green-light: #f0f7ff;
  --gold:        #f59e0b;
  --sidebar-w:   240px;
  --topbar-h:    56px;
}

/* ── Layout ─────────────────────────────────────────── */
body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; font-size: 14px; }

#sidebar {
  position: fixed; top: 0; left: 0; bottom: 0;
  width: var(--sidebar-w);
  background: linear-gradient(180deg, #0a192f 0%, #0f2744 55%, #162f52 100%);
  z-index: 1040;
  display: flex; flex-direction: column;
  transition: transform .3s;
  overflow-y: auto;
}
#sidebar::-webkit-scrollbar { width: 4px; }
#sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 2px; }

#topbar {
  position: fixed; top: 0;
  left: var(--sidebar-w); right: 0;
  height: var(--topbar-h);
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  z-index: 1030;
  display: flex; align-items: center;
  padding: 0 20px;
  gap: 12px;
  box-shadow: 0 1px 4px rgba(0,0,0,.06);
}

#main-content {
  margin-left: var(--sidebar-w);
  margin-top: var(--topbar-h);
  padding: 24px;
  min-height: calc(100vh - var(--topbar-h));
}

/* ── Sidebar brand ──────────────────────────────────── */
.sb-brand {
  padding: 18px 20px 14px;
  border-bottom: 1px solid rgba(255,255,255,.1);
  text-decoration: none;
  display: flex; align-items: center; gap: 12px;
}
.sb-brand-icon {
  width: 38px; height: 38px;
  background: rgba(255,255,255,.15);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; color: #fff; flex-shrink: 0;
}
.sb-brand-text { line-height: 1.2; }
.sb-brand-text strong { font-size: 13px; color: #fff; display: block; }
.sb-brand-text span   { font-size: 11px; color: rgba(255,255,255,.6); }

/* ── Sidebar nav ────────────────────────────────────── */
.sb-section { padding: 14px 12px 4px; }
.sb-section-label {
  font-size: 10px; font-weight: 600; letter-spacing: .08em;
  color: rgba(255,255,255,.4); text-transform: uppercase;
  padding: 0 8px; margin-bottom: 4px;
}
.sb-link {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 12px; border-radius: 8px;
  color: rgba(255,255,255,.75); text-decoration: none;
  font-size: 13px; transition: background .15s, color .15s;
  margin-bottom: 2px;
}
.sb-link i { font-size: 15px; width: 18px; text-align: center; flex-shrink: 0; }
.sb-link:hover  { background: rgba(255,255,255,.1); color: #fff; }
.sb-link.active { background: rgba(255,255,255,.18); color: #fff; font-weight: 600; }
.sb-link .badge { margin-left: auto; font-size: 10px; }

/* Submenu */
.sb-submenu { padding-left: 16px; display: none; }
.sb-submenu.open { display: block; }
.sb-submenu .sb-link { font-size: 12.5px; padding: 7px 10px; }
.sb-toggle .caret {
  margin-left: auto; font-size: 11px;
  transition: transform .2s;
}
.sb-toggle.open .caret { transform: rotate(90deg); }

/* ── Sidebar user ───────────────────────────────────── */
.sb-user {
  margin-top: auto;
  padding: 14px 16px;
  border-top: 1px solid rgba(255,255,255,.1);
  display: flex; align-items: center; gap: 10px;
}
.sb-user-avatar {
  width: 34px; height: 34px; border-radius: 50%;
  background: rgba(255,255,255,.2);
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; color: #fff; flex-shrink: 0; overflow: hidden;
}
.sb-user-avatar img { width: 100%; height: 100%; object-fit: cover; }
.sb-user-info { flex: 1; min-width: 0; }
.sb-user-info strong { font-size: 12px; color: #fff; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sb-user-info span { font-size: 11px; color: rgba(255,255,255,.55); }
.sb-logout { color: rgba(255,255,255,.5); font-size: 15px; text-decoration: none; }
.sb-logout:hover { color: #ff8080; }

/* ── Topbar ─────────────────────────────────────────── */
.topbar-toggle { border: none; background: none; font-size: 20px; color: #555; cursor: pointer; }
.topbar-title  { font-size: 16px; font-weight: 600; color: #1a2035; flex: 1; }
.topbar-notif  {
  position: relative; width: 36px; height: 36px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 8px; color: #555; cursor: pointer;
  text-decoration: none; transition: background .15s;
}
.topbar-notif:hover { background: #f0f2f5; color: #222; }
.topbar-notif .notif-badge {
  position: absolute; top: 4px; right: 4px;
  min-width: 16px; height: 16px; padding: 0 4px;
  background: #dc3545; border-radius: 8px;
  font-size: 9px; font-weight: 700; color: #fff;
  display: flex; align-items: center; justify-content: center;
  display: none;
}
.topbar-user {
  display: flex; align-items: center; gap: 8px;
  padding: 4px 10px 4px 4px;
  border-radius: 20px; cursor: pointer;
  border: none; background: none;
  transition: background .15s;
}
.topbar-user:hover { background: #f0f2f5; }
.topbar-user-avatar {
  width: 30px; height: 30px; border-radius: 50%;
  background: var(--green-light);
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; color: var(--green-dark); overflow: hidden;
}
.topbar-user-avatar img { width: 100%; height: 100%; object-fit: cover; }
.topbar-user-name { font-size: 13px; font-weight: 600; color: #333; }
.topbar-user-role { font-size: 11px; color: #888; }

/* ── Page card ──────────────────────────────────────── */
.page-card {
  background: #fff; border-radius: 12px;
  box-shadow: 0 1px 4px rgba(0,0,0,.06);
  padding: 20px 24px;
  margin-bottom: 20px;
}
.page-card-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 18px; padding-bottom: 14px;
  border-bottom: 1px solid #f0f2f5;
}
.page-card-header h5 { font-size: 15px; font-weight: 600; color: #1a2035; margin: 0; }

/* ── Mobile & Fast Touch ─────────────────────────────── */
*, html, body, a, button, .sb-link, .sb-toggle, .topbar-toggle {
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent;
}
.sb-link, .sb-toggle, .btn {
  user-select: none;
  -webkit-user-select: none;
  transition: background .12s, transform .08s;
}
.sb-link:active, .sb-toggle:active {
  background: rgba(255, 255, 255, 0.25) !important;
  transform: scale(0.97);
}
#topProgressBar {
  position: fixed;
  top: 0; left: 0; height: 3px;
  background: linear-gradient(90deg, #1a56db, #22c55e);
  z-index: 99999;
  width: 0%;
  opacity: 0;
  transition: width 0.25s ease, opacity 0.2s ease;
  pointer-events: none;
}
@media (max-width: 768px) {
  #sidebar { transform: translateX(-100%); }
  #sidebar.open { transform: translateX(0); }
  #topbar { left: 0; }
  #main-content { margin-left: 0; padding: 16px; }
  .sb-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.4); z-index: 1039;
  }
  .sb-overlay.active { display: block; }
}
</style>

<link rel="stylesheet" href="<?= Helper::asset('css/app.css') ?>">
<link rel="stylesheet" href="<?= Helper::asset('css/sidebar.css') ?>">
<link rel="stylesheet" href="<?= Helper::asset('css/upload.css') ?>">
<?php if (!empty($extraCss)) echo $extraCss; ?>
</head>
<body>

<!-- Top loading progress bar -->
<div id="topProgressBar"></div>

<!-- Sidebar overlay (mobile) -->
<div class="sb-overlay" id="sbOverlay"></div>

<!-- ── Sidebar ── -->
<nav id="sidebar">
  <a href="<?= Helper::baseUrl('dashboard') ?>" class="sb-brand">
    <div class="sb-brand-icon" style="background:#fff; padding:3px; border-radius:8px; width:36px; height:36px; display:flex; align-items:center; justify-content:center;">
      <img src="<?= Helper::baseUrl('img/logo-alwildan.png') ?>" alt="Logo Al Wildan" style="width:100%; height:100%; object-fit:contain;">
    </div>
    <div class="sb-brand-text">
      <strong style="color:#fff; font-size:13px; font-weight:700;">Inspeksi Kebersihan</strong>
      <span style="color:#93c5fd; font-size:10.5px; display:block; line-height:1.2;">Al-Wildan 8 Kemang</span>
    </div>
  </a>

  <?php
    $uri  = trim(parse_url($_GET['url'] ?? '', PHP_URL_PATH), '/');
    $role = Session::role();
    function sbLink(string $href, string $icon, string $label, string $uri): void {
      $active = ($uri === trim($href, '/')) ? ' active' : '';
      echo "<a href=\"" . Helper::baseUrl($href) . "\" class=\"sb-link{$active}\"><i class=\"bi bi-{$icon}\"></i>{$label}</a>";
    }
    function sbActive(string $prefix, string $uri): string {
      return str_starts_with($uri, $prefix) ? ' open active' : '';
    }
  ?>

  <div class="sb-section">
    <div class="sb-section-label">Utama</div>
    <?php sbLink('dashboard', 'speedometer2', 'Dashboard', $uri); ?>
    <?php sbLink('inspeksi', 'clipboard2-check', 'Inspeksi', $uri); ?>
    <?php sbLink('laporan', 'file-earmark-bar-graph', 'Laporan', $uri); ?>
    <?php sbLink('notifikasi', 'bell', 'Notifikasi', $uri); ?>
  </div>

  <?php if (in_array($role, ['admin', 'supervisor'])): ?>
  <div class="sb-section">
    <div class="sb-section-label">Master Data</div>
    <!-- Lokasi submenu -->
    <a href="#" class="sb-link sb-toggle<?= sbActive('gedung', $uri) ?>" data-target="sub-lokasi">
      <i class="bi bi-building"></i>Lokasi
      <i class="bi bi-chevron-right caret"></i>
    </a>
    <div class="sb-submenu<?= sbActive('gedung', $uri) ?: sbActive('lantai', $uri) ?: sbActive('ruangan', $uri) ?>" id="sub-lokasi">
      <?php sbLink('gedung',  'buildings',   'Gedung',   $uri); ?>
      <?php sbLink('lantai',  'layers',      'Lantai',   $uri); ?>
      <?php sbLink('ruangan', 'door-open',   'Ruangan',  $uri); ?>
    </div>
    <?php sbLink('standar', 'list-check', 'Standar Kebersihan', $uri); ?>
  </div>
  <?php endif; ?>

  <?php if ($role === 'admin'): ?>
  <div class="sb-section">
    <div class="sb-section-label">Manajemen</div>
    <?php sbLink('users',    'people',   'Pengguna',  $uri); ?>
    <?php sbLink('settings', 'gear',     'Pengaturan', $uri); ?>
    <?php sbLink('admin/activity-log', 'journal-text', 'Activity Log', $uri); ?>
  </div>
  <?php endif; ?>

  <!-- User info bawah -->
  <?php $u = Session::user(); ?>
  <div class="sb-user">
    <div class="sb-user-avatar">
      <?php if ($u['photo']): ?>
        <img src="<?= Helper::uploadUrl('profile/' . $u['photo']) ?>" alt="">
      <?php else: ?>
        <i class="bi bi-person"></i>
      <?php endif; ?>
    </div>
    <div class="sb-user-info">
      <strong><?= Helper::e($u['name']) ?></strong>
      <span><?= ucfirst($u['role']) ?></span>
    </div>
    <a href="<?= Helper::baseUrl('logout') ?>" class="sb-logout" title="Logout">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</nav>

<!-- ── Topbar ── -->
<header id="topbar">
  <button class="topbar-toggle" id="sbToggle" aria-label="Toggle sidebar">
    <i class="bi bi-list"></i>
  </button>
  <span class="topbar-title"><?= Helper::e($pageTitle ?? 'Dashboard') ?></span>

  <!-- Notifikasi -->
  <a href="<?= Helper::baseUrl('notifikasi') ?>" class="topbar-notif" id="notifBtn" title="Notifikasi">
    <i class="bi bi-bell" style="font-size:18px"></i>
    <span class="notif-badge" id="notifCount"></span>
  </a>

  <!-- User dropdown -->
  <div class="dropdown">
    <button class="topbar-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
      <div class="topbar-user-avatar">
        <?php if ($u['photo']): ?>
          <img src="<?= Helper::uploadUrl('profile/' . $u['photo']) ?>" alt="">
        <?php else: ?>
          <i class="bi bi-person-fill"></i>
        <?php endif; ?>
      </div>
      <div class="text-start d-none d-md-block">
        <div class="topbar-user-name"><?= Helper::e($u['name']) ?></div>
        <div class="topbar-user-role"><?= ucfirst($u['role']) ?></div>
      </div>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width:180px;border-radius:10px">
      <li><a class="dropdown-item" href="<?= Helper::baseUrl('users/profile') ?>"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
      <?php if ($role === 'admin'): ?>
      <li><a class="dropdown-item" href="<?= Helper::baseUrl('settings') ?>"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
      <?php endif; ?>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="<?= Helper::baseUrl('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
    </ul>
  </div>
</header>

<!-- ── Main Content ── -->
<main id="main-content">
  <?= Helper::flashAlert() ?>
  <?= $content ?? '' ?>
  <footer class="mt-5 py-3 text-center text-muted border-top" style="font-size: 12px;">
    &copy; <?= date('Y') ?> <strong>Al-Wildan Islamic School 8 Kemang Bogor</strong> &bull; Sistem Inspeksi Kebersihan
  </footer>
</main>

<!-- Scripts -->

<?php require APP_PATH . '/views/partials/gallery_modal.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ── Sidebar toggle ────────────────────────────────────
const sidebar   = document.getElementById('sidebar');
const overlay   = document.getElementById('sbOverlay');
const sbToggle  = document.getElementById('sbToggle');

sbToggle?.addEventListener('click', () => {
  sidebar.classList.toggle('open');
  overlay.classList.toggle('active');
});
overlay?.addEventListener('click', () => {
  sidebar.classList.remove('open');
  overlay.classList.remove('active');
});

// ── Submenu toggle (Instan tanpa delay) ───────────────
document.querySelectorAll('.sb-toggle').forEach(btn => {
  btn.addEventListener('click', e => {
    e.preventDefault();
    const target = document.getElementById(btn.dataset.target);
    btn.classList.toggle('open');
    target?.classList.toggle('open');
  });
});

// ── Top Progress Bar & Fast Navigation ────────────────
function startProgress() {
  const bar = document.getElementById('topProgressBar');
  if (bar) {
    bar.style.opacity = '1';
    bar.style.width = '75%';
  }
}
window.addEventListener('beforeunload', startProgress);

// Prefetch & instant feedback pada setiap link
document.querySelectorAll('a[href]').forEach(a => {
  const href = a.getAttribute('href');
  if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
    // Prefetch saat sentuh/hover agar load lebih cepat
    const prefetch = () => {
      if (!a.dataset.prefetched && href.includes(window.location.hostname)) {
        a.dataset.prefetched = '1';
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = href;
        document.head.appendChild(link);
      }
    };
    a.addEventListener('touchstart', prefetch, { passive: true });
    a.addEventListener('mouseenter', prefetch);

    // Respon klik langsung
    a.addEventListener('click', e => {
      if (!e.ctrlKey && !e.metaKey && !e.shiftKey && a.target !== '_blank') {
        startProgress();
        // Tutup sidebar jika di mobile setelah klik menu
        if (window.innerWidth <= 768 && a.classList.contains('sb-link') && !a.classList.contains('sb-toggle')) {
          sidebar.classList.remove('open');
          overlay.classList.remove('active');
        }
      }
    });
  }
});

// ── Notifikasi unread count ───────────────────────────
function loadNotifCount() {
  fetch('<?= Helper::baseUrl('notifikasi/unread') ?>', {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(data => {
    const badge = document.getElementById('notifCount');
    if (data.count > 0) {
      badge.textContent = data.count > 9 ? '9+' : data.count;
      badge.style.display = 'flex';
    } else {
      badge.style.display = 'none';
    }
  }).catch(() => {});
}
loadNotifCount();
setInterval(loadNotifCount, 60000); // refresh tiap 1 menit

// ── CSRF token global untuk fetch/AJAX ───────────────

// Base URL untuk JS fetch
window.BASE_URL = '<?= rtrim($this->appConfig["url"] ?? Helper::baseUrl(), "/") ?>';
window.CSRF_TOKEN = '<?= Session::csrfToken() ?>';

// ── SweetAlert delete confirm helper ─────────────────
function confirmDelete(formId) {
  Swal.fire({
    title: 'Hapus data ini?',
    text: 'Data yang dihapus tidak dapat dikembalikan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
  }).then(result => {
    if (result.isConfirmed) document.getElementById(formId).submit();
  });
}

// ── DataTable default ─────────────────────────────────
$.extend(true, $.fn.dataTable.defaults, {
  language: {
    url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json'
  },
  pageLength: 15,
  responsive: true,
});
</script>

<script src="<?= Helper::asset('js/app.js') ?>"></script>
<script src="<?= Helper::asset('js/upload.js') ?>"></script>

<!-- PWA Service Worker Registration & Install Banner -->
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('<?= Helper::baseUrl('sw.js') ?>')
      .then(reg => console.log('[PWA] ServiceWorker registered with scope:', reg.scope))
      .catch(err => console.warn('[PWA] ServiceWorker registration failed:', err));
  });
}

// PWA Install Prompt Banner
let deferredPrompt;
window.addEventListener('beforeinstallprompt', e => {
  e.preventDefault();
  deferredPrompt = e;
  const pwaBanner = document.getElementById('pwaInstallBanner');
  if (pwaBanner && !sessionStorage.getItem('pwa_dismissed')) {
    pwaBanner.classList.remove('d-none');
  }
});

function installPWA() {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(choiceResult => {
      if (choiceResult.outcome === 'accepted') {
        console.log('[PWA] User accepted install');
      }
      deferredPrompt = null;
      document.getElementById('pwaInstallBanner')?.classList.add('d-none');
    });
  }
}

function dismissPWABanner() {
  document.getElementById('pwaInstallBanner')?.classList.add('d-none');
  sessionStorage.setItem('pwa_dismissed', '1');
}

// Deteksi Khusus iPhone / iPad (iOS Safari PWA)
const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
const isStandalone = window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches;

if (isIos && !isStandalone && !sessionStorage.getItem('ios_pwa_dismissed')) {
  window.addEventListener('load', () => {
    document.getElementById('iosInstallBanner')?.classList.remove('d-none');
  });
}

function dismissIosBanner() {
  document.getElementById('iosInstallBanner')?.classList.add('d-none');
  sessionStorage.setItem('ios_pwa_dismissed', '1');
}
</script>

<!-- Floating PWA Install Banner (Android & Chrome) -->
<div id="pwaInstallBanner" class="d-none position-fixed bottom-0 start-50 translate-middle-x mb-3 p-3 bg-white rounded-3 shadow-lg border border-primary border-opacity-25" style="z-index:9999; max-width:92%; width:420px">
  <div class="d-flex align-items-center gap-3">
    <img src="<?= Helper::baseUrl('icons/icon-192x192.png') ?>" alt="Inspeksi" class="rounded-3" style="width:44px; height:44px">
    <div class="flex-grow-1">
      <div class="fw-bold small text-dark">Pasang Aplikasi Inspeksi</div>
      <div class="text-muted" style="font-size:11px">Akses lebih cepat & mudah dari layar utama HP Anda</div>
    </div>
    <div class="d-flex gap-1">
      <button type="button" class="btn btn-sm btn-success px-2 py-1" onclick="installPWA()" style="font-size:12px">
        <i class="bi bi-download me-1"></i>Pasang
      </button>
      <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1" onclick="dismissPWABanner()" style="font-size:12px">
        ✕
      </button>
    </div>
  </div>
</div>

<!-- Floating PWA Install Banner Khusus iPhone / iPad (iOS Safari) -->
<div id="iosInstallBanner" class="d-none position-fixed bottom-0 start-50 translate-middle-x mb-3 p-3 bg-white rounded-3 shadow-lg border border-primary border-opacity-25" style="z-index:9999; max-width:92%; width:420px">
  <div class="d-flex align-items-start gap-3">
    <img src="<?= Helper::baseUrl('icons/icon-192x192.png') ?>" alt="Inspeksi" class="rounded-3 shadow-sm" style="width:44px; height:44px">
    <div class="flex-grow-1">
      <div class="fw-bold small text-dark mb-1"><i class="bi bi-apple me-1"></i>Pasang di iPhone / iPad</div>
      <div class="text-muted" style="font-size:11.5px; line-height:1.4">
        1. Ketuk tombol <strong>Bagikan</strong> (<i class="bi bi-box-arrow-up text-primary fw-bold"></i>) di bawah Safari.<br>
        2. Gulir ke bawah lalu pilih <strong>"Tambah ke Layar Utama"</strong> (<i class="bi bi-plus-square text-success"></i>).
      </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1" onclick="dismissIosBanner()" style="font-size:12px">
      ✕
    </button>
  </div>
</div>

<?php if (!empty($extraJs)) echo $extraJs; ?>
</body>
</html>
