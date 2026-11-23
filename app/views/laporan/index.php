<?php
$unitList   = array_merge(['' => 'Semua Unit'], Helper::unitList());
$statusList = [''=>'Semua Status','bersih'=>'Bersih','kurang_bersih'=>'Kurang Bersih','kotor'=>'Kotor'];
?>

<!-- Filter Card -->
<div class="page-card mb-3">
  <div class="page-card-header">
    <h5 class="mb-0"><i class="bi bi-funnel me-2 text-success"></i>Filter Laporan</h5>
    <button class="btn btn-sm btn-outline-secondary" type="button"
            data-bs-toggle="collapse" data-bs-target="#filterPanel">
      <i class="bi bi-chevron-down"></i>
    </button>
  </div>
  <div id="filterPanel" class="collapse show">
    <form method="GET" action="<?= Helper::baseUrl('laporan') ?>" id="filterForm">
      <div class="row g-2 mt-1">
        <!-- Tanggal -->
        <div class="col-sm-6 col-md-3">
          <label class="form-label small fw-semibold">Dari Tanggal</label>
          <input type="date" name="tanggal_dari" class="form-control form-control-sm"
                 value="<?= Helper::e($filters['tanggal_dari']) ?>">
        </div>
        <div class="col-sm-6 col-md-3">
          <label class="form-label small fw-semibold">Sampai Tanggal</label>
          <input type="date" name="tanggal_sampai" class="form-control form-control-sm"
                 value="<?= Helper::e($filters['tanggal_sampai']) ?>">
        </div>
        <!-- Unit -->
        <div class="col-sm-6 col-md-2">
          <label class="form-label small fw-semibold">Unit</label>
          <select name="unit" class="form-select form-select-sm">
            <?php foreach ($unitList as $v=>$l): ?>
              <option value="<?= $v ?>" <?= $filters['unit']==$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Gedung -->
        <div class="col-sm-6 col-md-4">
          <label class="form-label small fw-semibold">Gedung</label>
          <select name="gedung_id" class="form-select form-select-sm">
            <option value="">Semua Gedung</option>
            <?php foreach ($gedung as $g): ?>
              <option value="<?= $g['id'] ?>" <?= $filters['gedung_id']==$g['id']?'selected':'' ?>>
                [<?= strtoupper($g['unit']) ?>] <?= Helper::e($g['nama']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Status -->
        <div class="col-sm-6 col-md-2">
          <label class="form-label small fw-semibold">Status</label>
          <select name="status" class="form-select form-select-sm">
            <?php foreach ($statusList as $v=>$l): ?>
              <option value="<?= $v ?>" <?= $filters['status']==$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Petugas -->
        <div class="col-sm-6 col-md-4">
          <label class="form-label small fw-semibold">Petugas Inspeksi</label>
          <select name="petugas_id" class="form-select form-select-sm">
            <option value="">Semua Petugas</option>
            <?php foreach ($petugas as $p): ?>
              <option value="<?= $p['id'] ?>" <?= $filters['petugas_id']==$p['id']?'selected':'' ?>>
                <?= Helper::e($p['name']) ?> (<?= ucfirst($p['role']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Skor range -->
        <div class="col-sm-3 col-md-2">
          <label class="form-label small fw-semibold">Skor Min</label>
          <input type="number" name="skor_min" class="form-control form-control-sm"
                 min="0" max="100" value="<?= Helper::e($filters['skor_min']) ?>" placeholder="0">
        </div>
        <div class="col-sm-3 col-md-2">
          <label class="form-label small fw-semibold">Skor Maks</label>
          <input type="number" name="skor_max" class="form-control form-control-sm"
                 min="0" max="100" value="<?= Helper::e($filters['skor_max']) ?>" placeholder="100">
        </div>
        <!-- Aksi filter -->
        <div class="col-12 d-flex gap-2 mt-2">
          <button type="submit" class="btn btn-success btn-sm">
            <i class="bi bi-search me-1"></i>Tampilkan
          </button>
          <a href="<?= Helper::baseUrl('laporan') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-circle me-1"></i>Reset
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Ringkasan + Export -->
<div class="page-card mb-3">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

    <!-- Ringkasan angka -->
    <div class="d-flex gap-3 flex-wrap align-items-center">
      <div class="text-center px-3 py-2 rounded-3 bg-light">
        <div class="fw-bold fs-4"><?= $ringkasan['total'] ?></div>
        <div class="small text-muted">Total</div>
      </div>
      <div class="text-center px-3 py-2 rounded-3 bg-success bg-opacity-10">
        <div class="fw-bold fs-4 text-success"><?= $ringkasan['bersih'] ?></div>
        <div class="small text-muted">Bersih</div>
      </div>
      <div class="text-center px-3 py-2 rounded-3 bg-warning bg-opacity-10">
        <div class="fw-bold fs-4 text-warning"><?= $ringkasan['kurang_bersih'] ?></div>
        <div class="small text-muted">Kurang</div>
      </div>
      <div class="text-center px-3 py-2 rounded-3 bg-danger bg-opacity-10">
        <div class="fw-bold fs-4 text-danger"><?= $ringkasan['kotor'] ?></div>
        <div class="small text-muted">Kotor</div>
      </div>
      <div class="text-center px-3 py-2 rounded-3 border">
        <div class="fw-bold fs-4 text-<?= Helper::skorBadgeClass($ringkasan['avg_skor']) ?>">
          <?= number_format($ringkasan['avg_skor'],1) ?>
        </div>
        <div class="small text-muted">Avg Skor</div>
      </div>
    </div>

    <!-- Tombol export -->
    <?php if (Session::isRole(['admin','supervisor'])): ?>
    <div class="d-flex gap-2 flex-wrap">
      <a href="<?= Helper::baseUrl('laporan/export-excel?' . http_build_query($filters)) ?>"
         class="btn btn-success btn-sm">
        <i class="bi bi-file-earmark-excel me-1"></i>Export Excel (CSV)
      </a>
      <a href="<?= Helper::baseUrl('laporan/export-pdf?' . http_build_query($filters)) ?>"
         class="btn btn-danger btn-sm" target="_blank">
        <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
      </a>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Tabel hasil -->
<div class="page-card">
  <div class="page-card-header">
    <h5 class="mb-0"><i class="bi bi-table me-2 text-success"></i>Hasil Laporan
      <span class="badge bg-secondary ms-2"><?= count($rows) ?> data</span>
    </h5>
  </div>
  <div class="table-responsive">
    <table id="tblLaporan" class="table table-hover align-middle" style="width:100%">
      <thead class="table-light">
        <tr>
          <th>No</th><th>Tanggal</th><th>Ruangan</th><th>Gedung</th><th>Unit</th>
          <th>Petugas PJ</th><th>Inspektor</th>
          <th class="text-center">Skor</th><th class="text-center">Status</th>
          <th class="text-center">Foto</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="11" class="text-center text-muted py-4">
          Tidak ada data untuk filter yang dipilih.
        </td></tr>
      <?php endif; ?>
      <?php $no=1; foreach ($rows as $r):
        $sc = Helper::skorBadgeClass((float)($r['skor_total']??0));
        $hasRuangan = !empty($r['foto_ruangan']);
        $fotoItemCount = (int)($r['total_foto_item'] ?? 0);
        $totalFoto = ($hasRuangan ? 1 : 0) + $fotoItemCount;
      ?>
        <tr>
          <td class="text-muted small"><?= $no++ ?></td>
          <td class="small"><?= Helper::dateId($r['tanggal']) ?></td>
          <td>
            <div class="fw-semibold small"><?= Helper::e($r['ruangan_nama']) ?></div>
            <div class="text-muted" style="font-size:11px"><?= Helper::e($r['ruangan_kode']) ?></div>
          </td>
          <td class="small"><?= Helper::e($r['gedung_nama']) ?></td>
          <td><?= Helper::unitBadge($r['gedung_unit'] ?? '') ?></td>
          <td class="small"><?= $r['assigned_nama'] ? Helper::e($r['assigned_nama']) : '<span class="text-muted">—</span>' ?></td>
          <td class="small fw-semibold"><?= Helper::e($r['inspected_nama'] ?? '—') ?></td>
          <td class="text-center">
            <span class="fw-bold text-<?= $sc ?>"><?= $r['skor_total'] !== null ? number_format((float)$r['skor_total'],1) : '—' ?></span>
          </td>
          <td class="text-center"><?= Helper::statusBadge($r['status'] ?? '') ?></td>
          <td class="text-center">
            <?php if ($totalFoto > 0): ?>
              <div class="d-inline-flex align-items-center gap-1">
                <?php if ($hasRuangan): ?>
                  <img src="<?= Helper::uploadUrl("inspeksi/{$r['id']}/{$r['foto_ruangan']}") ?>"
                       alt="Thumbnail" class="rounded border shadow-xs"
                       style="width:36px; height:36px; object-fit:cover; cursor:pointer;"
                       onclick="loadGallery(<?= $r['id'] ?>, '<?= Helper::e($r['ruangan_nama']) ?>')"
                       title="Klik lihat foto ruangan"
                       onerror="this.style.display='none'">
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 py-1 px-2 rounded-2"
                        onclick="loadGallery(<?= $r['id'] ?>, '<?= Helper::e($r['ruangan_nama']) ?>')" title="Lihat <?= $totalFoto ?> Foto Dokumentasi">
                  <i class="bi bi-camera-fill"></i>
                  <span style="font-size:11px"><?= $totalFoto ?> Foto</span>
                </button>
              </div>
            <?php else: ?>
              <button type="button" class="btn btn-sm btn-light border text-muted d-inline-flex align-items-center gap-1 py-1 px-2 rounded-2"
                      onclick="loadGallery(<?= $r['id'] ?>, '<?= Helper::e($r['ruangan_nama']) ?>')" title="Lihat status dokumentasi foto">
                <i class="bi bi-camera text-muted"></i>
                <span style="font-size:11px">0 Foto</span>
              </button>
            <?php endif; ?>
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= Helper::baseUrl("inspeksi/detail/{$r['id']}") ?>"
                 class="btn btn-sm btn-outline-primary" title="Detail Inspeksi">
                <i class="bi bi-eye"></i>
              </a>
              <button type="button" class="btn btn-sm btn-outline-info" title="Galeri Foto"
                      onclick="loadGallery(<?= $r['id'] ?>, '<?= Helper::e($r['ruangan_nama']) ?>')">
                <i class="bi bi-images"></i>
              </button>
              <a href="<?= Helper::baseUrl("laporan/print?id={$r['id']}") ?>"
                 class="btn btn-sm btn-outline-secondary" title="Cetak / PDF" target="_blank">
                <i class="bi bi-printer"></i>
              </a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once APP_PATH . '/views/partials/gallery_modal.php'; ?>

<script>
$('#tblLaporan').DataTable({
  columnDefs:[{orderable:false,targets:-1}],
  order:[[1,'desc']],
  pageLength: 25,
});
</script>
