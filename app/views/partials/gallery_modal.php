<?php
/**
 * Partial: gallery_modal.php
 * Include sekali di layout atau di halaman yang butuh gallery.
 * Trigger: openGallery(url) atau loadGallery(inspeksiId)
 */
?>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content bg-dark border-0">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title text-white">
          <i class="bi bi-images me-2"></i>
          <span id="galleryTitle">Galeri Foto</span>
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-2">
        <!-- Single photo view -->
        <div id="galleryMain" class="text-center mb-2 d-none">
          <img id="galleryMainImg" src="" class="img-fluid rounded" style="max-height:65vh;object-fit:contain">
          <div id="galleryCaption" class="text-white-50 small mt-2"></div>
        </div>
        <!-- Grid view -->
        <div id="galleryGrid" class="row g-2"></div>
        <!-- Loading -->
        <div id="galleryLoading" class="text-center py-4 text-white d-none">
          <div class="spinner-border spinner-border-sm me-2"></div>Memuat foto...
        </div>
        <!-- Empty -->
        <div id="galleryEmpty" class="text-center py-4 text-white-50 d-none">
          <i class="bi bi-image fs-3 d-block mb-2"></i>Belum ada foto
        </div>
      </div>
    </div>
  </div>
</div>
