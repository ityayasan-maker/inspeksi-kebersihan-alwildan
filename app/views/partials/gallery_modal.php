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
