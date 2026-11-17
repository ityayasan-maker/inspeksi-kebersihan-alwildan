<?php
/**
 * Skeleton cards — tampil saat data dashboard sedang dimuat
 * Usage: <?php include APP_PATH . '/views/partials/skeleton_cards.php'; ?>
 */
?>
<div class="row g-3 mb-4" id="skeletonStats">
  <?php for ($i = 0; $i < 4; $i++): ?>
  <div class="col-6 col-md-3">
    <div class="page-card stat-card-skeleton">
      <div class="d-flex justify-content-between mb-3">
        <div class="flex-grow-1">
          <div class="skeleton skeleton-text mb-2" style="width:75px"></div>
          <div class="skeleton" style="width:50px;height:28px;border-radius:4px"></div>
        </div>
        <div class="skeleton skeleton-circle" style="width:42px;height:42px"></div>
      </div>
      <div class="skeleton skeleton-text" style="width:55px"></div>
    </div>
  </div>
  <?php endfor; ?>
</div>
