<?php
$statusOpt = [''=>'Semua Status','bersih'=>'Bersih','kurang_bersih'=>'Kurang Bersih','kotor'=>'Kotor'];
?>
<div class="page-card">
  <div class="page-card-header">
    <h5><i class="bi bi-clipboard2-check me-2 text-success"></i>Daftar Inspeksi</h5>
    <a href="<?= Helper::baseUrl('inspeksi/create') ?>" class="btn btn-success btn-sm">
      <i class="bi bi-plus-lg me-1"></i>Inspeksi Baru
    </a>
  </div>

  <!-- Filter -->
  <form method="GET" action="<?= Helper::baseUrl('inspeksi') ?>" class="row g-2 mb-3">
    <div class="col-sm-3">
      <select name="status" class="form-select form-select-sm">
        <?php foreach ($statusOpt as $v => $l): ?>
          <option value="<?= $v ?>" <?= ($filters['status']==$v)?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-3">
      <input type="date" name="tanggal_dari" class="form-control form-control-sm"
             value="<?= Helper::e($filters['tanggal_dari']) ?>" placeholder="Dari tanggal">
    </div>
    <div class="col-sm-3">
      <input type="date" name="tanggal_sampai" class="form-control form-control-sm"
             value="<?= Helper::e($filters['tanggal_sampai']) ?>" placeholder="Sampai tanggal">
    </div>
    <div class="col-sm-3 d-flex gap-2">
      <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-search me-1"></i>Filter</button>
      <a href="<?= Helper::baseUrl('inspeksi') ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
  </form>

  <!-- Tabel -->
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th><th>Tanggal</th><th>Ruangan</th><th>Gedung</th>
          <th>Petugas PJ</th><th>Diinspeksi Oleh</th>
          <th class="text-center">Skor</th><th class="text-center">Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($paginator['data'])): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">Belum ada data inspeksi.</td></tr>
      <?php endif; ?>
      <?php foreach ($paginator['data'] as $row): ?>
        <tr>
          <td class="text-muted small"><?= $row['id'] ?></td>
          <td class="small">
            <?= Helper::dateId($row['tanggal']) ?>
            <?php if ($row['is_draft']): ?>
              <span class="badge bg-secondary ms-1">Draft</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="fw-semibold small"><?= Helper::e($row['ruangan_nama']) ?></div>
            <div class="text-muted" style="font-size:11px"><?= Helper::e($row['ruangan_kode']) ?></div>
          </td>
          <td class="small"><?= Helper::e($row['gedung_nama']) ?></td>
          <td class="small"><?= $row['assigned_nama'] ? Helper::e($row['assigned_nama']) : '<span class="text-muted">—</span>' ?></td>
          <td class="small fw-semibold"><?= Helper::e($row['inspected_nama']) ?></td>
          <td class="text-center">
            <?php if ($row['skor_total'] !== null): ?>
              <span class="fw-bold text-<?= Helper::skorBadgeClass((float)$row['skor_total']) ?>">
                <?= number_format($row['skor_total'], 1) ?>
              </span>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <?= $row['status'] ? Helper::statusBadge($row['status']) : '<span class="text-muted small">Draft</span>' ?>
          </td>
          <td>
            <a href="<?= Helper::baseUrl("inspeksi/detail/{$row['id']}") ?>" class="btn btn-sm btn-outline-primary me-1" title="Detail">
              <i class="bi bi-eye"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-info me-1" title="Galeri Foto" onclick="loadGallery(<?= $row['id'] ?>, '<?= Helper::e($row['ruangan_nama']) ?>')">
              <i class="bi bi-images"></i>
            </button>
            <?php if (Session::isRole(['admin','supervisor']) || ($row['is_draft'] && $row['inspected_by_id'] == Session::get('user_id'))): ?>
            <a href="<?= Helper::baseUrl("inspeksi/edit/{$row['id']}") ?>" class="btn btn-sm btn-outline-warning me-1" title="Edit">
              <i class="bi bi-pencil"></i>
            </a>
            <?php endif; ?>
            <?php if (Session::isRole(['admin','supervisor'])): ?>
            <form id="del-i<?= $row['id'] ?>" method="POST" action="<?= Helper::baseUrl('inspeksi/delete') ?>" class="d-inline">
              <?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('del-i<?= $row['id'] ?>')">
                <i class="bi bi-trash"></i>
              </button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="d-flex align-items-center justify-content-between mt-2">
    <small class="text-muted">
      Total: <?= $paginator['total'] ?> inspeksi
    </small>
    <?= Helper::paginationLinks($paginator, Helper::baseUrl('inspeksi')) ?>
  </div>
</div>

<?php require_once APP_PATH . '/views/partials/gallery_modal.php'; ?>
