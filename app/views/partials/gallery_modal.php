<?php
/**
 * Partial: gallery_modal.php
 * Modal Galeri Foto Inspeksi Kebersihan
 * Trigger: loadGallery(inspeksiId, ruanganNama) atau openGallery(url, caption)
 */
?>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content bg-dark text-white border-0 shadow-lg" style="border-radius:14px; overflow:hidden">
      <!-- Modal Header -->
      <div class="modal-header border-bottom border-secondary border-opacity-25 py-2 px-3">
        <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
          <button type="button" class="btn btn-sm btn-outline-light d-none py-1 px-2" id="galleryBtnBack" onclick="showGalleryGrid()" title="Kembali ke Galeri">
            <i class="bi bi-arrow-left me-1"></i>Daftar Foto
          </button>
          <h6 class="modal-title mb-0 text-truncate" id="galleryTitle">
            <i class="bi bi-images me-2 text-info"></i>Galeri Foto Inspeksi
          </h6>
        </div>
        <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-3">
        <!-- 1. Loading State -->
        <div id="galleryLoading" class="text-center py-5 d-none">
          <div class="spinner-border text-info mb-2" role="status"></div>
          <div class="text-white-50 small">Memuat foto inspeksi...</div>
        </div>

        <!-- 2. Empty State -->
        <div id="galleryEmpty" class="text-center py-5 d-none">
          <i class="bi bi-image text-white-50 display-4 d-block mb-3"></i>
          <h6 class="text-white-50">Belum Ada Foto Dokumentasi</h6>
          <p class="text-secondary small mb-0">Inspeksi ini tidak memiliki foto ruangan atau foto temuan checklist.</p>
        </div>

        <!-- 3. Grid View -->
        <div id="galleryGridWrap">
          <div id="galleryGrid" class="row g-2"></div>
        </div>

        <!-- 4. Single Photo View (Lightbox) -->
        <div id="galleryMain" class="d-none">
          <div class="position-relative text-center bg-black rounded p-1 mb-2 d-flex align-items-center justify-content-center" style="min-height:280px; max-height:68vh">
            <img id="galleryMainImg" src="" class="img-fluid rounded" style="max-height:65vh; max-width:100%; object-fit:contain" alt="Foto Inspeksi">
            
            <!-- Prev / Next Nav Buttons -->
            <button type="button" id="btnPrevPhoto" class="btn btn-dark bg-opacity-75 position-absolute start-0 top-50 translate-middle-y ms-2 rounded-circle p-2 d-none" onclick="navGalleryPhoto(-1)" title="Foto Sebelumnya" style="width:42px;height:42px">
              <i class="bi bi-chevron-left fs-5"></i>
            </button>
            <button type="button" id="btnNextPhoto" class="btn btn-dark bg-opacity-75 position-absolute end-0 top-50 translate-middle-y me-2 rounded-circle p-2 d-none" onclick="navGalleryPhoto(1)" title="Foto Selanjutnya" style="width:42px;height:42px">
              <i class="bi bi-chevron-right fs-5"></i>
            </button>
          </div>

          <!-- Caption & Info Bar -->
          <div class="p-2 bg-secondary bg-opacity-25 rounded d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <div id="galleryCaption" class="fw-bold text-white small"></div>
              <div id="gallerySubCaption" class="text-white-50" style="font-size:12px"></div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span id="galleryCounter" class="badge bg-secondary"></span>
              <a id="galleryDownloadBtn" href="#" target="_blank" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size:11.5px">
                <i class="bi bi-box-arrow-up-right me-1"></i>Buka Penuh
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ── Global Gallery Modal Handler ──
let currentGalleryPhotos = [];
let currentGalleryIndex  = 0;

if (typeof window.escH !== 'function') {
  window.escH = function(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  };
}

/**
 * Buka Galeri Foto untuk satu sesi inspeksi (Menampilkan Foto Ruangan & Foto Item Temuan)
 */
window.loadGallery = function(inspeksiId, ruanganNama = '') {
  const modalEl = document.getElementById('galleryModal');
  if (!modalEl) {
    console.warn('#galleryModal tidak ditemukan');
    return;
  }
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  // Reset UI
  document.getElementById('galleryMain').classList.add('d-none');
  document.getElementById('galleryGridWrap').classList.remove('d-none');
  document.getElementById('galleryBtnBack').classList.add('d-none');
  document.getElementById('galleryGrid').innerHTML = '';
  document.getElementById('galleryLoading').classList.remove('d-none');
  document.getElementById('galleryEmpty').classList.add('d-none');
  
  const titleText = 'Galeri Foto Inspeksi #' + inspeksiId + (ruanganNama ? ' &mdash; ' + window.escH(ruanganNama) : '');
  document.getElementById('galleryTitle').innerHTML = '<i class="bi bi-images me-2 text-info"></i>' + titleText;
  modal.show();

  const baseUrl = (window.BASE_URL || '<?= Helper::baseUrl() ?>').replace(/\/+$/, '');
  fetch(baseUrl + '/upload/gallery/' + inspeksiId, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('galleryLoading').classList.add('d-none');
    currentGalleryPhotos = data.photos || [];

    if (!currentGalleryPhotos.length) {
      document.getElementById('galleryEmpty').classList.remove('d-none');
      return;
    }

    const grid = document.getElementById('galleryGrid');
    const badgeClass = k => k === 'bersih' ? 'success' : (k === 'kurang_bersih' ? 'warning text-dark' : 'danger');
    const statusLabel = k => k === 'bersih' ? 'Bersih' : (k === 'kurang_bersih' ? 'Kurang' : 'Kotor');

    currentGalleryPhotos.forEach((p, idx) => {
      const isUtamaBadge = p.is_utama ? '<span class="badge bg-primary position-absolute top-0 start-0 m-1" style="font-size:9.5px"><i class="bi bi-camera me-1"></i>Ruangan</span>' : '';
      grid.innerHTML += `
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card bg-dark border border-secondary border-opacity-50 overflow-hidden shadow-sm h-100" style="cursor:pointer; transition:transform .15s" onclick="viewGalleryPhoto(${idx})" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            <div class="position-relative" style="aspect-ratio: 4/3; background:#111">
              ${isUtamaBadge}
              <img src="${p.url}" class="w-100 h-100" style="object-fit:cover" alt="${window.escH(p.aspek)}" onerror="this.onerror=null; this.src='data:image/svg+xml;charset=UTF-8,<svg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'120\\' height=\\'90\\' fill=\\'%23555\\'><rect width=\\'120\\' height=\\'90\\' fill=\\'%23222\\'/><text x=\\'50%\\' y=\\'50%\\' fill=\\'%23888\\' text-anchor=\\'middle\\' font-size=\\'12\\'>Foto</text></svg>'">
            </div>
            <div class="p-2" style="background:rgba(20,25,35,0.95)">
              <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                <span class="badge bg-${badgeClass(p.kondisi)}" style="font-size:9.5px">${statusLabel(p.kondisi)}</span>
                ${p.is_utama ? '<span class="text-white-50" style="font-size:9.5px">Utama</span>' : ''}
              </div>
              <div class="text-white text-truncate fw-semibold" style="font-size:11.5px" title="${window.escH(p.aspek)}">${window.escH(p.aspek)}</div>
              ${p.catatan ? `<div class="text-white-50 text-truncate mt-1" style="font-size:10.5px"><i class="bi bi-chat-text me-1"></i>${window.escH(p.catatan)}</div>` : ''}
            </div>
          </div>
        </div>`;
    });
  })
  .catch(err => {
    console.error('Gallery load error:', err);
    document.getElementById('galleryLoading').classList.add('d-none');
    document.getElementById('galleryEmpty').classList.remove('d-none');
  });
};

/**
 * Tampilkan foto tunggal (lightbox view)
 */
window.viewGalleryPhoto = function(idx) {
  if (!currentGalleryPhotos || !currentGalleryPhotos[idx]) return;
  currentGalleryIndex = idx;
  const p = currentGalleryPhotos[idx];

  document.getElementById('galleryGridWrap').classList.add('d-none');
  document.getElementById('galleryMain').classList.remove('d-none');
  document.getElementById('galleryBtnBack').classList.remove('d-none');

  document.getElementById('galleryMainImg').src = p.url;
  document.getElementById('galleryCaption').textContent = p.aspek || 'Foto Dokumentasi';
  document.getElementById('gallerySubCaption').innerHTML = 
    (p.is_utama ? '<span class="badge bg-primary me-1">Foto Ruangan</span> ' : '') + 
    (p.kondisi ? '<span class="badge bg-' + (p.kondisi === 'bersih' ? 'success' : (p.kondisi === 'kurang_bersih' ? 'warning text-dark' : 'danger')) + ' me-1">' + (p.kondisi === 'bersih' ? 'Bersih' : (p.kondisi === 'kurang_bersih' ? 'Kurang Bersih' : 'Kotor')) + '</span> ' : '') +
    (p.catatan ? '<span class="text-light fst-italic">"' + window.escH(p.catatan) + '"</span>' : '');
  
  document.getElementById('galleryCounter').textContent = `${idx + 1} / ${currentGalleryPhotos.length}`;
  document.getElementById('galleryDownloadBtn').href = p.url;

  // Tombol navigasi prev / next
  const btnPrev = document.getElementById('btnPrevPhoto');
  const btnNext = document.getElementById('btnNextPhoto');
  if (currentGalleryPhotos.length > 1) {
    btnPrev?.classList.remove('d-none');
    btnNext?.classList.remove('d-none');
  } else {
    btnPrev?.classList.add('d-none');
    btnNext?.classList.add('d-none');
  }
};

/**
 * Navigasi foto sebelumnya (-1) atau selanjutnya (+1)
 */
window.navGalleryPhoto = function(dir) {
  let newIdx = currentGalleryIndex + dir;
  if (newIdx < 0) newIdx = currentGalleryPhotos.length - 1;
  if (newIdx >= currentGalleryPhotos.length) newIdx = 0;
  window.viewGalleryPhoto(newIdx);
};

/**
 * Kembali dari single photo view ke tampilan grid
 */
window.showGalleryGrid = function() {
  document.getElementById('galleryMain').classList.add('d-none');
  document.getElementById('galleryGridWrap').classList.remove('d-none');
  document.getElementById('galleryBtnBack').classList.add('d-none');
};

/**
 * Buka foto tunggal langsung di modal gallery
 */
window.openGallery = function(url, caption = '') {
  const modalEl = document.getElementById('galleryModal');
  if (!modalEl) {
    window.open(url, '_blank');
    return;
  }
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  currentGalleryPhotos = [{
    url: url,
    aspek: caption || 'Foto Dokumentasi',
    catatan: '',
    is_utama: false,
    kondisi: ''
  }];
  currentGalleryIndex = 0;

  document.getElementById('galleryTitle').innerHTML = '<i class="bi bi-image me-2 text-info"></i>Foto Dokumentasi';
  document.getElementById('galleryLoading').classList.add('d-none');
  document.getElementById('galleryEmpty').classList.add('d-none');

  window.viewGalleryPhoto(0);
  document.getElementById('galleryBtnBack')?.classList.add('d-none');
  document.getElementById('btnPrevPhoto')?.classList.add('d-none');
  document.getElementById('btnNextPhoto')?.classList.add('d-none');

  modal.show();
};

window.showPhoto = function(src, caption = '') {
  window.openGallery(src, caption);
};
</script>
