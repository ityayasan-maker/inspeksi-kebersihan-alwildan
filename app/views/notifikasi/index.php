<div class="page-card">
  <div class="page-card-header">
    <h5 class="mb-0"><i class="bi bi-bell me-2 text-success"></i>Notifikasi</h5>
    <span class="badge bg-secondary"><?= count($notifs) ?> item</span>
  </div>

  <?php if (empty($notifs)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
      Tidak ada notifikasi.
    </div>
  <?php else: ?>

  <div class="list-group list-group-flush">
    <?php foreach ($notifs as $n):
      $ic = match($n['type']){
        'alert_kotor'      => 'exclamation-triangle-fill text-danger',
        'alert_threshold'  => 'exclamation-circle-fill text-warning',
        'reminder'         => 'clock-fill text-primary',
        default            => 'info-circle-fill text-info'
      };
      $bg = !$n['is_read'] ? 'bg-light' : '';
    ?>
    <div class="list-group-item <?= $bg ?> px-0 py-3">
      <div class="d-flex gap-3 align-items-start">

        <!-- Icon -->
        <div class="flex-shrink-0 mt-1" style="width:36px;text-align:center;font-size:18px">
          <i class="bi bi-<?= $ic ?>"></i>
        </div>

        <!-- Content -->
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
            <div class="fw-semibold small"><?= Helper::e($n['title']) ?></div>
            <div class="text-muted" style="font-size:11px;white-space:nowrap">
              <?= Helper::formatDatetime($n['created_at']) ?>
            </div>
          </div>
          <div class="text-muted small mt-1"><?= Helper::e($n['message'] ?? '') ?></div>

          <!-- Info ruangan (jika terkait inspeksi) -->
          <?php if ($n['ruangan_nama'] || $n['gedung_nama']): ?>
          <div class="mt-1" style="font-size:11px">
            <i class="bi bi-building me-1 text-muted"></i>
            <span class="text-muted">
              <?= Helper::e($n['ruangan_nama'] ?? '') ?>
              <?= $n['gedung_nama'] ? ' — ' . Helper::e($n['gedung_nama']) : '' ?>
            </span>
          </div>
          <?php endif; ?>
        </div>

        <!-- CTA -->
        <?php if ($n['inspeksi_id']): ?>
        <a href="<?= Helper::baseUrl("inspeksi/detail/{$n['inspeksi_id']}") ?>"
           class="btn btn-sm btn-outline-primary flex-shrink-0">
          <i class="bi bi-eye me-1"></i>Lihat
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>
</div>
