/**
 * app.js — Global UI utilities
 * Loading states, skeleton, toast, dark mode, mobile UX
 */

/* ── Loading Overlay ────────────────────────────────────────── */
const LoadingUI = {
  overlay: null,

  init() {
    if (document.getElementById('loadingOverlay')) return;
    const el = document.createElement('div');
    el.id = 'loadingOverlay';
    el.className = 'loading-overlay';
    el.innerHTML = `
      <div class="loading-spinner">
        <div class="spinner-border" role="status"></div>
        <p id="loadingMsg">Memuat...</p>
      </div>`;
    document.body.appendChild(el);
    this.overlay = el;
  },

  show(msg = 'Memuat...') {
    this.init();
    document.getElementById('loadingMsg').textContent = msg;
    this.overlay.classList.add('active');
  },

  hide() {
    this.overlay?.classList.remove('active');
  }
};

/* ── Toast Notification ─────────────────────────────────────── */
const Toast = {
  container: null,

  init() {
    if (document.getElementById('toastContainer')) {
      this.container = document.getElementById('toastContainer');
      return;
    }
    this.container = document.createElement('div');
    this.container.id = 'toastContainer';
    this.container.className = 'toast-container position-fixed top-0 end-0 p-3';
    this.container.style.zIndex = '11000';
    document.body.appendChild(this.container);
  },

  show(type, title, message = '', duration = 3500) {
    this.init();
    const id    = 'toast_' + Date.now();
    const icons = { success: 'check-circle-fill', error: 'x-circle-fill',
                    warning: 'exclamation-triangle-fill', info: 'info-circle-fill' };
    const colors = { success: '#166534', error: '#991b1b', warning: '#854d0e', info: '#1e40af' };
    const bg    = { success: '#dcfce7', error: '#fee2e2', warning: '#fef9c3', info: '#dbeafe' };

    const el = document.createElement('div');
    el.id = id;
    el.className = `toast toast-custom toast-${type} show align-items-center`;
    el.setAttribute('role', 'alert');
    el.innerHTML = `
      <div class="toast-header" style="background:${bg[type]||'#f0f0f0'};color:${colors[type]||'#333'}">
        <i class="bi bi-${icons[type]||'info-circle'} me-2"></i>
        <strong class="me-auto">${escH(title)}</strong>
        <button type="button" class="btn-close btn-close-sm" onclick="this.closest('.toast').remove()"></button>
      </div>
      ${message ? `<div class="toast-body small">${escH(message)}</div>` : ''}`;

    el.style.opacity = '0';
    el.style.transform = 'translateX(20px)';
    el.style.transition = 'opacity .25s, transform .25s';
    this.container.appendChild(el);

    requestAnimationFrame(() => {
      el.style.opacity = '1';
      el.style.transform = 'translateX(0)';
    });

    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateX(20px)';
      setTimeout(() => el.remove(), 300);
    }, duration);
  },

  success(title, msg) { this.show('success', title, msg); },
  error(title, msg)   { this.show('error',   title, msg); },
  warning(title, msg) { this.show('warning', title, msg); },
  info(title, msg)    { this.show('info',    title, msg); },
};

/* ── Skeleton Loader ────────────────────────────────────────── */
const Skeleton = {
  /** Ganti elemen dengan skeleton placeholder */
  show(selector, rows = 3) {
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!el) return;
    el.dataset.originalContent = el.innerHTML;
    let html = '';
    for (let i = 0; i < rows; i++) {
      html += `
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="skeleton skeleton-circle skeleton-avatar"></div>
          <div class="flex-grow-1">
            <div class="skeleton skeleton-text mb-2" style="width:${60+Math.random()*30}%"></div>
            <div class="skeleton skeleton-text" style="width:${30+Math.random()*20}%"></div>
          </div>
          <div class="skeleton skeleton-badge"></div>
        </div>`;
    }
    el.innerHTML = html;
  },

  /** Kembalikan konten asli */
  hide(selector) {
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!el || !el.dataset.originalContent) return;
    el.innerHTML = el.dataset.originalContent;
    delete el.dataset.originalContent;
  },

  /** Kartu statistik skeleton */
  statsCards(containerId, count = 4) {
    const container = document.getElementById(containerId);
    if (!container) return;
    let html = '';
    for (let i = 0; i < count; i++) {
      html += `
        <div class="col-6 col-md-3">
          <div class="page-card stat-card-skeleton">
            <div class="d-flex justify-content-between mb-3">
              <div>
                <div class="skeleton skeleton-text mb-2" style="width:80px"></div>
                <div class="skeleton" style="width:48px;height:32px;border-radius:4px"></div>
              </div>
              <div class="skeleton skeleton-circle" style="width:44px;height:44px"></div>
            </div>
            <div class="skeleton skeleton-text" style="width:60px"></div>
          </div>
        </div>`;
    }
    container.innerHTML = html;
  },

  tableRows(tableBodyId, cols = 6, rows = 5) {
    const tbody = document.getElementById(tableBodyId);
    if (!tbody) return;
    let html = '';
    for (let i = 0; i < rows; i++) {
      html += '<tr>';
      for (let j = 0; j < cols; j++) {
        html += `<td><div class="skeleton skeleton-text" style="width:${50+Math.random()*40}%"></div></td>`;
      }
      html += '</tr>';
    }
    tbody.innerHTML = html;
  }
};

/* ── Form Loading State ─────────────────────────────────────── */
function formLoadingState(formEl, loading = true, msg = 'Menyimpan...') {
  const btn = formEl.querySelector('[type="submit"]');
  if (!btn) return;
  if (loading) {
    btn.dataset.originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>${escH(msg)}`;
  } else {
    btn.disabled = false;
    btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
  }
}

/* ── Confirm Delete (SweetAlert) ────────────────────────────── */
function confirmDelete(formId, msg = 'Data yang dihapus tidak dapat dikembalikan.') {
  Swal.fire({
    title: 'Hapus data ini?',
    text: msg,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, Hapus',
    cancelButtonText: 'Batal',
    borderRadius: '12px',
  }).then(result => {
    if (result.isConfirmed) document.getElementById(formId)?.submit();
  });
}

/* ── AJAX helper ────────────────────────────────────────────── */
async function fetchJSON(url, options = {}) {
  const defaults = {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-Token': window.CSRF_TOKEN || '',
    }
  };
  const res = await fetch(url, { ...defaults, ...options,
    headers: { ...defaults.headers, ...(options.headers || {}) }
  });
  if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
  return res.json();
}

/* ── Page transitions (Instant render) ─────────────────────── */
function initPageAnimations() {
  // Fast display without artificial delays
}

/* ── Mobile sidebar handling ────────────────────────────────── */
function initMobileSidebar() {
  // Ditangani secara responsif di layout_main.php
}

/* ── Auto dismiss alerts ────────────────────────────────────── */
function initAutoDismissAlerts() {
  document.querySelectorAll('.alert.alert-success, .alert.alert-info').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity .5s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    }, 5000);
  });
}

/* ── Responsive table hint ──────────────────────────────────── */
function initTableHints() {
  if (window.innerWidth < 768) {
    document.querySelectorAll('.table-responsive').forEach(wrap => {
      if (wrap.scrollWidth > wrap.clientWidth) {
        const hint = document.createElement('div');
        hint.className = 'text-muted small text-center mt-1';
        hint.innerHTML = '<i class="bi bi-arrow-left-right me-1"></i>Geser untuk melihat semua';
        wrap.after(hint);
        setTimeout(() => hint.remove(), 4000);
      }
    });
  }
}

/* ── Util ────────────────────────────────────────────────────── */
function escH(s) {
  return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;')
         .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function debounce(fn, delay = 300) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}

function formatNumber(n, decimals = 0) {
  return Number(n).toLocaleString('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

/* ── Init on DOMContentLoaded ───────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  localStorage.removeItem('inspeksi_dark_mode');
  document.body.classList.remove('dark-mode');
  initPageAnimations();
  initMobileSidebar();
  initAutoDismissAlerts();
  initTableHints();

  // Auto loading state untuk semua form submit
  document.querySelectorAll('form:not([data-no-loading])').forEach(form => {
    form.addEventListener('submit', function() {
      // Jangan aktifkan loading untuk form delete
      if (!this.id?.startsWith('del-') && !this.id?.startsWith('formReset')) {
        formLoadingState(this, true);
      }
    });
  });

  // Tooltip Bootstrap
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el, { trigger: 'hover' });
  });
});

/* ── Expose globals ─────────────────────────────────────────── */
window.LoadingUI   = LoadingUI;
window.Toast       = Toast;
window.Skeleton    = Skeleton;
window.confirmDelete = confirmDelete;
window.fetchJSON   = fetchJSON;
window.escH        = escH;
window.debounce    = debounce;
window.formatNumber= formatNumber;
