<?php
$statusClass = match($inspeksi['status'] ?? '') {
    'bersih'        => 'success',
    'kurang_bersih' => 'warning',
    'kotor'         => 'danger',
    default         => 'secondary',
};
$skor = (float)($inspeksi['skor_total'] ?? 0);
?>

<!-- Header card -->
<div class="page-card mb-3">
  <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
    <div>
      <div class="d-flex align-items-center gap-2 mb-2">
        <h5 class="mb-0 fw-bold">Inspeksi #<?= $inspeksi['id'] ?></h5>
        <?= $inspeksi['is_draft'] ? '<span class="badge bg-secondary">Draft</span>' : '' ?>
        <?= $inspeksi['status'] ? Helper::statusBadge($inspeksi['status']) : '' ?>
      </div>
      <div class="d-flex gap-3 flex-wrap text-muted small">
        <span><i class="bi bi-calendar3 me-1"></i><?= Helper::dateId($inspeksi['tanggal']) ?></span>
        <span><i class="bi bi-clock me-1"></i><?= $inspeksi['waktu_mulai'] ?? '—' ?></span>
        <span><i class="bi bi-building me-1"></i><?= Helper::e($inspeksi['gedung_nama']) ?></span>
        <span><i class="bi bi-door-open me-1"></i><?= Helper::e($inspeksi['ruangan_nama']) ?></span>
      </div>
    </div>

    <!-- Skor besar -->
    <div class="text-center p-3 rounded-3 bg-<?= $statusClass ?> bg-opacity-10 border border-<?= $statusClass ?> border-opacity-25" style="min-width:110px">
      <div class="fw-bold text-<?= $statusClass ?>" style="font-size:2.2rem;line-height:1"><?= $skor > 0 ? number_format($skor, 1) : '—' ?></div>
      <div class="small text-muted mt-1">Skor Rata-rata</div>
      <?php if ($inspeksi['status']): ?>
        <div class="small fw-semibold text-<?= $statusClass ?> mt-1"><?= Helper::statusLabel($inspeksi['status']) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Meta info -->
  <div class="row g-2 mt-3">
    <div class="col-sm-6 col-md-3">
      <div class="p-2 bg-light rounded-2 small">
        <div class="text-muted">Petugas PJ Area</div>
        <div class="fw-semibold"><?= $inspeksi['assigned_nama'] ? Helper::e($inspeksi['assigned_nama']) : '<span class="text-muted">—</span>' ?></div>
      </div>
    </div>
    <div class="col-sm-6 col-md-3">
      <div class="p-2 bg-light rounded-2 small">
        <div class="text-muted">Diinspeksi Oleh</div>
        <div class="fw-semibold"><?= Helper::e($inspeksi['inspected_nama']) ?></div>
      </div>
    </div>
    <div class="col-sm-6 col-md-3">
      <div class="p-2 bg-light rounded-2 small">
        <div class="text-muted">Total Checklist</div>
        <div class="fw-semibold"><?= count($details) ?> item</div>
      </div>
    </div>
    <div class="col-sm-6 col-md-3">
      <div class="p-2 bg-light rounded-2 small">
        <div class="text-muted">Disimpan</div>
        <div class="fw-semibold"><?= Helper::formatDatetime($inspeksi['created_at']) ?></div>
      </div>
    </div>
  </div>

  <!-- Aksi -->
  <div class="mt-3 d-flex gap-2 flex-wrap">
    <a href="<?= Helper::baseUrl('inspeksi') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <?php if (Session::isRole(['admin','supervisor']) || ($inspeksi['is_draft'] && $inspeksi['inspected_by_id'] == Session::get('user_id'))): ?>
    <a href="<?= Helper::baseUrl("inspeksi/edit/{$inspeksi['id']}") ?>" class="btn btn-outline-warning btn-sm">
      <i class="bi bi-pencil me-1"></i>Edit
    </a>
    <?php endif; ?>
    <a href="<?= Helper::baseUrl("laporan/print?id={$inspeksi['id']}") ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
      <i class="bi bi-printer me-1"></i>Cetak
    </a>
    <button class="btn btn-outline-info btn-sm" onclick="loadGallery(<?= $inspeksi['id'] ?>)">
      <i class="bi bi-images me-1"></i>Galeri Foto
    </button>
  </div>

  <?php if (!empty($inspeksi['foto_ruangan'])): ?>
  <div class="mt-3 p-3 bg-light rounded-3 d-flex align-items-center justify-content-between gap-3 border flex-wrap">
    <div class="d-flex align-items-center gap-3">
      <img src="<?= Helper::uploadUrl("inspeksi/{$inspeksi['id']}/{$inspeksi['foto_ruangan']}") ?>"
           class="rounded-3 border shadow-sm" style="height:85px; width:115px; object-fit:cover; cursor:pointer"
           onclick="openGallery(this.src, 'Dokumentasi Kondisi Ruangan', 'Ruangan', 'bg-primary')"
           alt="Foto Ruangan">
      <div>
        <div class="fw-bold small text-dark"><i class="bi bi-camera-fill me-1 text-success"></i>Foto Dokumentasi Ruangan</div>
        <div class="text-muted" style="font-size:11.5px">Kondisi umum ruangan saat inspeksi dimulai. Klik foto untuk perbesar full-screen.</div>
      </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-success" onclick="loadGallery(<?= (int)$inspeksi['id'] ?>)">
      <i class="bi bi-images me-1"></i>Buka Galeri Foto
    </button>
  </div>
  <?php endif; ?>
</div>

<div class="row g-3">
  <!-- Checklist detail -->
  <div class="col-lg-8">
    <div class="page-card">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-success"></i>Hasil Checklist</h6>
        <!-- Summary badge -->
        <div class="d-flex gap-1">
          <?php
          $b = $k = $d = 0;
          foreach ($details as $det) {
            if ($det['kondisi']==='bersih') $b++;
            elseif ($det['kondisi']==='kurang_bersih') $k++;
            else $d++;
          }
          ?>
          <span class="badge bg-success"><?= $b ?> Bersih</span>
          <span class="badge bg-warning text-dark"><?= $k ?> Kurang</span>
          <span class="badge bg-danger"><?= $d ?> Kotor</span>
        </div>
      </div>

      <?php if (empty($details)): ?>
        <p class="text-muted">Belum ada detail checklist.</p>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($details as $i => $det):
          $bc = match($det['kondisi']){'bersih'=>'success','kurang_bersih'=>'warning',default=>'danger'};
          $badgeCls = $bc === 'success' ? 'bg-success' : ($bc === 'warning' ? 'bg-warning text-dark' : 'bg-danger');
        ?>
        <div class="list-group-item px-0 py-3">
          <div class="d-flex align-items-start gap-3">
            <span class="badge bg-<?= $bc ?> mt-1" style="min-width:28px;text-align:center"><?= $i+1 ?></span>
            <div class="flex-grow-1">
              <div class="fw-semibold small text-dark"><?= Helper::e($det['aspek']) ?></div>
              <?php if (!empty($det['catatan'])): ?>
                <div class="text-muted mt-1" style="font-size:12px">
                  <i class="bi bi-chat-text me-1 text-primary"></i><?= Helper::e($det['catatan']) ?>
                </div>
              <?php endif; ?>
              <?php if (!empty($det['foto'])): ?>
                <div class="mt-2 d-flex align-items-center gap-2">
                  <img src="<?= Helper::uploadUrl("inspeksi/{$inspeksi['id']}/{$det['foto']}") ?>"
                       class="rounded-2 border shadow-xs" style="height:65px; width:85px; object-fit:cover; cursor:pointer"
                       onclick="openGallery(this.src, '<?= Helper::e($det['aspek']) ?>', '<?= Helper::statusLabel($det['kondisi']) ?>', '<?= $badgeCls ?>')"
                       alt="Bukti foto">
                  <span class="badge bg-light text-dark border small" style="font-size:11px">
                    <i class="bi bi-camera-fill text-success me-1"></i>Bukti Foto
                  </span>
                </div>
              <?php else: ?>
                <div class="mt-1 text-muted" style="font-size:11px">
                  <i class="bi bi-camera text-secondary me-1"></i><span class="text-muted opacity-75">Tanpa foto bukti</span>
                </div>
              <?php endif; ?>
            </div>
            <div class="text-end flex-shrink-0">
              <span class="badge bg-<?= $bc ?>"><?= Helper::statusLabel($det['kondisi']) ?></span>
              <div class="small fw-bold text-<?= $bc ?> mt-1"><?= $det['skor'] ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Catatan umum -->
      <?php if ($inspeksi['catatan_umum']): ?>
      <div class="mt-3 p-3 bg-light rounded-2">
        <div class="small fw-semibold text-muted mb-1"><i class="bi bi-chat-dots me-1"></i>Catatan Umum</div>
        <div class="small"><?= nl2br(Helper::e($inspeksi['catatan_umum'])) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Sidebar: histori & chart mini -->
  <div class="col-lg-4">
    <div class="page-card">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Histori Ruangan</h6>
      </div>
      <?php if (empty($history)): ?>
        <p class="text-muted small">Belum ada histori.</p>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($history as $h):
          $hc = match($h['status']??''){'bersih'=>'success','kurang_bersih'=>'warning','kotor'=>'danger',default=>'secondary'};
        ?>
        <a href="<?= Helper::baseUrl("inspeksi/detail/{$h['id']}") ?>"
           class="list-group-item list-group-item-action px-0 py-2 <?= $h['id']==$inspeksi['id'] ? 'active' : '' ?>">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="small fw-semibold"><?= Helper::dateId($h['tanggal']) ?></div>
              <div class="small text-muted"><?= Helper::e($h['inspected_nama'] ?? '—') ?></div>
            </div>
            <div class="text-end">
              <span class="badge bg-<?= $hc ?>"><?= $h['skor_total'] ? number_format($h['skor_total'],1) : '—' ?></span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal foto -->
<div class="modal fade" id="modalFoto" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark">
      <div class="modal-body p-1 text-center">
        <img id="fotoModalImg" src="" class="img-fluid rounded" style="max-height:80vh">
      </div>
    </div>
  </div>
</div>
<script>
function showPhoto(src) {
  document.getElementById('fotoModalImg').src = src;
  new bootstrap.Modal(document.getElementById('modalFoto')).show();
}
</script>
