<?php
$tipeColor = fn($t) => match($t) {
    'kelas','lab_ipa','lab_komputer' => 'primary',
    'toilet','tempat_wudhu'          => 'info',
    'kantor','ruang_rapat'           => 'warning',
    'ruang_ibadah'                   => 'success',
    'lapangan','area_luar'           => 'secondary',
    default                          => 'light text-dark border'
};
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="<?= Helper::baseUrl('ruangan') ?>">Ruangan</a></li>
    <li class="breadcrumb-item active"><?= Helper::e($ruangan['nama']) ?></li>
  </ol>
</nav>

<!-- Info ruangan -->
<div class="page-card mb-3">
  <div class="d-flex align-items-center gap-3 flex-wrap">
    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10"
         style="width:52px;height:52px;font-size:22px;color:var(--green-main)">
      <i class="bi bi-door-open"></i>
    </div>
    <div class="flex-grow-1">
      <h5 class="mb-1 fw-bold"><?= Helper::e($ruangan['nama']) ?></h5>
      <div class="d-flex gap-2 flex-wrap">
        <span class="badge bg-secondary"><?= Helper::e($ruangan['kode']) ?></span>
        <span class="badge bg-<?= $tipeColor($ruangan['tipe']) ?>"><?= Helper::e($tipeList[$ruangan['tipe']] ?? $ruangan['tipe']) ?></span>
        <span class="badge bg-light text-dark border"><?= Helper::e($ruangan['gedung_nama']) ?> — <?= Helper::e($ruangan['lantai_nama']) ?></span>
        <?php if ($hasOverride): ?>
          <span class="badge bg-warning text-dark"><i class="bi bi-pencil-square me-1"></i>Custom Override</span>
        <?php else: ?>
          <span class="badge bg-light text-muted border"><i class="bi bi-arrow-repeat me-1"></i>Default Tipe</span>
        <?php endif; ?>
      </div>
    </div>
    <div class="d-flex gap-2">
      <!-- Copy dari ruangan lain -->
      <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCopy">
        <i class="bi bi-copy me-1"></i>Copy dari Ruangan Lain
      </button>
      <?php if ($hasOverride): ?>
        <!-- Reset ke default -->
        <form method="POST" action="<?= Helper::baseUrl("checklist/{$ruangan['id']}/reset") ?>" id="formReset">
          <?= Helper::csrfField() ?>
          <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete('formReset')">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset ke Default
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-3">

  <!-- ── Kolom Kiri: Checklist Efektif ── -->
  <div class="col-lg-7">
    <div class="page-card h-100">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold">
          <i class="bi bi-clipboard2-check me-2 text-success"></i>
          Checklist Aktif
          <span class="badge bg-success ms-2"><?= count($effective) ?> item</span>
        </h6>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalBulkAssign">
          <i class="bi bi-sliders me-1"></i>Kelola Checklist
        </button>
      </div>

      <?php if (empty($effective)): ?>
        <div class="alert alert-warning mb-0">
          <i class="bi bi-exclamation-triangle me-2"></i>
          Belum ada checklist. Klik <strong>Kelola Checklist</strong> untuk menambahkan.
        </div>
      <?php else: ?>
        <div class="list-group list-group-flush">
          <?php foreach ($effective as $i => $item): ?>
            <div class="list-group-item px-0 py-2 d-flex align-items-start gap-2">
              <span class="badge bg-light text-muted border mt-1" style="min-width:28px;text-align:center"><?= $i+1 ?></span>
              <div class="flex-grow-1">
                <div class="fw-semibold small"><?= Helper::e($item['aspek']) ?></div>
                <?php if ($item['deskripsi']): ?>
                  <div class="text-muted" style="font-size:12px"><?= Helper::e($item['deskripsi']) ?></div>
                <?php endif; ?>
              </div>
              <div class="d-flex flex-column gap-1 align-items-end flex-shrink-0">
                <span class="badge bg-<?= $tipeColor($item['tipe_ruangan']) ?>" style="font-size:10px">
                  <?= Helper::e($tipeList[$item['tipe_ruangan']] ?? $item['tipe_ruangan']) ?>
                </span>
                <span class="badge bg-light text-dark border" style="font-size:10px">
                  <?= Helper::e($frekList[$item['frekuensi']] ?? $item['frekuensi']) ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Kolom Kanan: Tambah Item + Override List ── -->
  <div class="col-lg-5">

    <!-- Tambah 1 item -->
    <div class="page-card mb-3">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-success"></i>Tambah Item</h6>
      </div>
      <form method="POST" action="<?= Helper::baseUrl("checklist/{$ruangan['id']}/add") ?>">
        <?= Helper::csrfField() ?>
        <div class="mb-2">
          <label class="form-label small fw-semibold">Filter Tipe</label>
          <select id="filterTipe" class="form-select form-select-sm">
            <option value="">Semua Tipe</option>
            <?php foreach ($tipeList as $k => $v): ?>
              <option value="<?= $k ?>" <?= ($k === $ruangan['tipe']) ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Standar Kebersihan</label>
          <select name="standar_id" id="selStandar" class="form-select form-select-sm" required>
            <option value="">-- Pilih standar --</option>
            <?php
            $grouped_avail = [];
            foreach ($available as $a) $grouped_avail[$a['tipe_ruangan']][] = $a;
            foreach ($grouped_avail as $tipe => $items):
            ?>
              <optgroup label="<?= Helper::e($tipeList[$tipe] ?? $tipe) ?>" data-tipe="<?= $tipe ?>">
                <?php foreach ($items as $a): ?>
                  <option value="<?= $a['id'] ?>" data-tipe="<?= $a['tipe_ruangan'] ?>"><?= Helper::e($a['aspek']) ?></option>
                <?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-success btn-sm w-100">
          <i class="bi bi-plus-lg me-1"></i>Tambahkan
        </button>
      </form>
    </div>

    <!-- Override list (jika ada) -->
    <?php if ($hasOverride && !empty($assigned)): ?>
    <div class="page-card">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Override Items</h6>
        <span class="badge bg-warning text-dark"><?= count($assigned) ?> item</span>
      </div>
      <div style="max-height:300px;overflow-y:auto">
        <?php foreach ($assigned as $item): ?>
          <div class="d-flex align-items-center gap-2 py-2 border-bottom">
            <div class="flex-grow-1">
              <div class="small fw-semibold <?= !$item['is_active'] ? 'text-decoration-line-through text-muted' : '' ?>">
                <?= Helper::e($item['aspek']) ?>
              </div>
              <div style="font-size:11px" class="text-muted"><?= Helper::e($tipeList[$item['tipe_ruangan']] ?? $item['tipe_ruangan']) ?></div>
            </div>
            <form method="POST" action="<?= Helper::baseUrl("checklist/{$ruangan['id']}/toggle") ?>" class="d-inline">
              <?= Helper::csrfField() ?>
              <input type="hidden" name="id" value="<?= $item['id'] ?>">
              <button type="submit" class="btn btn-sm <?= $item['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" style="font-size:11px;padding:2px 8px">
                <?= $item['is_active'] ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>' ?>
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>


<!-- ── Modal Bulk Assign ── -->
<div class="modal fade" id="modalBulkAssign" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" action="<?= Helper::baseUrl("checklist/{$ruangan['id']}/assign") ?>">
        <?= Helper::csrfField() ?>
        <div class="modal-header">
          <h6 class="modal-title fw-bold"><i class="bi bi-sliders me-2"></i>Kelola Checklist — <?= Helper::e($ruangan['nama']) ?></h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex gap-2 mb-3 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-success" onclick="checkAll(true)">Centang Semua</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkAll(false)">Hapus Semua</button>
            <select id="bulkFilterTipe" class="form-select form-select-sm" style="width:auto">
              <option value="">Semua Tipe</option>
              <?php foreach ($tipeList as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($k === $ruangan['tipe']) ? 'selected':'' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Semua standar aktif, dikelompokkan per tipe -->
          <?php
          // Ambil SEMUA standar aktif beserta status apakah sudah di-assign
          $assignedIds = array_column($assigned, 'standar_id');
          $allStandar  = (new StandarModel())->query(
              "SELECT * FROM standar_kebersihan WHERE is_active=1 ORDER BY tipe_ruangan, no_urut"
          );
          $groupedAll  = [];
          foreach ($allStandar as $s) $groupedAll[$s['tipe_ruangan']][] = $s;
          ?>
          <?php foreach ($groupedAll as $tipe => $items): ?>
            <div class="mb-3 bulk-group" data-tipe="<?= $tipe ?>">
              <div class="fw-semibold small mb-2 text-muted text-uppercase" style="letter-spacing:.05em">
                <i class="bi bi-tag me-1"></i><?= Helper::e($tipeList[$tipe] ?? $tipe) ?>
              </div>
              <div class="row g-2">
                <?php foreach ($items as $s): ?>
                  <div class="col-md-6 bulk-item" data-tipe="<?= $tipe ?>">
                    <label class="d-flex align-items-start gap-2 p-2 rounded border cursor-pointer hover-bg <?= in_array($s['id'], $assignedIds) ? 'border-success bg-success bg-opacity-10' : '' ?>"
                           style="cursor:pointer">
                      <input type="checkbox" name="standar_ids[]" value="<?= $s['id'] ?>"
                             class="bulk-check mt-1 flex-shrink-0"
                             <?= in_array($s['id'], $assignedIds) ? 'checked' : '' ?>>
                      <div>
                        <div class="small fw-semibold"><?= Helper::e($s['aspek']) ?></div>
                        <?php if ($s['deskripsi']): ?>
                          <div class="text-muted" style="font-size:11px"><?= Helper::e(Helper::truncate($s['deskripsi'], 70)) ?></div>
                        <?php endif; ?>
                        <span class="badge bg-light text-dark border mt-1" style="font-size:10px">
                          <?= Helper::e($frekList[$s['frekuensi']] ?? $s['frekuensi']) ?>
                        </span>
                      </div>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="modal-footer">
          <span id="checkCount" class="text-muted small me-auto">
            <?= count($assignedIds) ?> item dipilih
          </span>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success btn-sm">
            <i class="bi bi-check-lg me-1"></i>Simpan Checklist
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- ── Modal Copy dari Ruangan Lain ── -->
<div class="modal fade" id="modalCopy" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="POST" action="<?= Helper::baseUrl('checklist/copy') ?>">
        <?= Helper::csrfField() ?>
        <input type="hidden" name="target_ruangan_id" value="<?= $ruangan['id'] ?>">
        <div class="modal-header">
          <h6 class="modal-title fw-bold"><i class="bi bi-copy me-2"></i>Copy Checklist</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-3">Checklist ruangan sumber akan menggantikan checklist ruangan ini.</p>
          <label class="form-label fw-semibold small">Ruangan Sumber</label>
          <select name="source_ruangan_id" class="form-select form-select-sm" required>
            <option value="">-- Pilih Ruangan --</option>
            <?php foreach ($allRuangan as $r):
              if ($r['id'] == $ruangan['id']) continue; ?>
              <option value="<?= $r['id'] ?>">[<?= strtoupper($r['gedung_unit']) ?>] <?= Helper::e($r['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-warning btn-sm">
            <i class="bi bi-copy me-1"></i>Copy & Timpa
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
// Filter dropdown tambah item
document.getElementById('filterTipe').addEventListener('change', function() {
  const tipe = this.value;
  document.querySelectorAll('#selStandar optgroup').forEach(og => {
    og.style.display = (!tipe || og.dataset.tipe === tipe) ? '' : 'none';
  });
  document.getElementById('selStandar').value = '';
});
// Trigger filter saat load
document.getElementById('filterTipe').dispatchEvent(new Event('change'));

// Bulk assign: filter tipe
document.getElementById('bulkFilterTipe').addEventListener('change', function() {
  const tipe = this.value;
  document.querySelectorAll('.bulk-group').forEach(g => {
    g.style.display = (!tipe || g.dataset.tipe === tipe) ? '' : 'none';
  });
});
document.getElementById('bulkFilterTipe').dispatchEvent(new Event('change'));

// Check all / uncheck all
function checkAll(state) {
  document.querySelectorAll('.bulk-check:not([disabled])').forEach(c => {
    const visible = c.closest('.bulk-item').style.display !== 'none';
    if (visible) c.checked = state;
  });
  updateCount();
}

// Count selected
function updateCount() {
  const n = document.querySelectorAll('.bulk-check:checked').length;
  document.getElementById('checkCount').textContent = n + ' item dipilih';
}
document.querySelectorAll('.bulk-check').forEach(c => c.addEventListener('change', updateCount));
updateCount();
</script>
