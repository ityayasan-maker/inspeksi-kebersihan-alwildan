<?php
/**
 * Partial: gallery_modal.php
 * Include sekali di layout atau di halaman yang butuh gallery.
 * Trigger: openGallery(url) atau loadGallery(inspeksiId)
 */
?>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark border-0 shadow-lg text-white" style="border-radius:12px; overflow:hidden">
      <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
        <h6 class="modal-title text-white fw-bold d-flex align-items-center gap-2 mb-0">
          <i class="bi bi-images text-success"></i>
          <span id="galleryTitle">Galeri Dokumentasi Foto</span>
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3">
        <!-- Single photo view -->
        <div id="galleryMain" class="text-center d-none">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <button type="button" class="btn btn-sm btn-outline-light py-1 px-3" onclick="backToGrid()" style="font-size:12px">
              <i class="bi bi-grid-3x3 me-1"></i>Lihat Semua Foto
            </button>
            <span id="galleryMainBadge" class="badge bg-success"></span>
          </div>
          <div class="p-2 bg-black rounded-3 d-flex align-items-center justify-content-center" style="min-height:280px">
            <img id="galleryMainImg" src="" class="img-fluid rounded" style="max-height:65vh; object-fit:contain">
          </div>
          <div id="galleryCaption" class="text-white-50 small mt-2 fw-medium"></div>
        </div>

        <!-- Grid view -->
        <div id="galleryGrid" class="row g-2"></div>

        <!-- Loading -->
        <div id="galleryLoading" class="text-center py-5 text-white d-none">
          <div class="spinner-border text-success spinner-border-sm me-2" role="status"></div>
          <span class="small">Memuat dokumentasi foto...</span>
        </div>

        <!-- Empty -->
        <div id="galleryEmpty" class="text-center py-5 text-white-50 d-none">
          <i class="bi bi-camera-slash fs-1 d-block mb-2 text-muted"></i>
          <div class="fw-semibold text-white mb-1">Belum Ada Foto Dokumentasi</div>
          <div class="small text-white-50" style="max-width:320px; margin:0 auto">
            Foto suasana ruangan atau bukti temuan checklist belum diunggah untuk inspeksi ini.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
