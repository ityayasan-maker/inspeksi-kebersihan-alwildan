<div class="page-card">
  <div class="page-card-header">
    <h5><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Inspeksi #<?= $inspeksi['id'] ?></h5>
    <a href="<?= Helper::baseUrl("inspeksi/detail/{$inspeksi['id']}") ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
  </div>

  <!-- Info ruangan (readonly) -->
  <div class="alert alert-light border mb-4 small">
    <strong>Ruangan:</strong> <?= Helper::e($inspeksi['ruangan_nama']) ?>
    &nbsp;|&nbsp;<strong>Gedung:</strong> <?= Helper::e($inspeksi['gedung_nama']) ?>
    &nbsp;|&nbsp;<strong>Petugas PJ:</strong> <?= $inspeksi['assigned_nama'] ? Helper::e($inspeksi['assigned_nama']) : '—' ?>
  </div>

  <form method="POST" action="<?= Helper::baseUrl("inspeksi/edit/{$inspeksi['id']}") ?>" enctype="multipart/form-data">
    <?= Helper::csrfField() ?>

    <div class="row g-3 mb-4">
      <div class="col-md-5">
        <label class="form-label fw-semibold">Penanggung Jawab / Petugas Area</label>
        <select name="assigned_user_id" class="form-select">
          <option value="">-- Pilih Penanggung Jawab --</option>
          <?php foreach ($petugas as $p): ?>
            <option value="<?= $p['id'] ?>" <?= (($inspeksi['assigned_user_id'] ?? '') == $p['id']) ? 'selected' : '' ?>>
              <?= Helper::e($p['name']) ?> (<?= ucfirst($p['role']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Tanggal</label>
        <input type="date" name="tanggal" class="form-control"
               value="<?= $inspeksi['tanggal'] ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Waktu Mulai</label>
        <input type="time" name="waktu_mulai" class="form-control"
               value="<?= $inspeksi['waktu_mulai'] ?? '' ?>">
      </div>
    </div>

    <!-- Checklist edit -->
    <h6 class="fw-bold mb-3 border-bottom pb-2">
      <i class="bi bi-list-check me-2 text-success"></i>
      Checklist (<?= count($checklist) ?> item)
    </h6>

    <?php
    $detailMap = [];
    foreach ($details as $d) $detailMap[$d['standar_id']] = $d;
    $currentTipe = '';
    foreach ($checklist as $i => $item):
      if ($item['tipe_ruangan'] !== $currentTipe):
        if ($currentTipe !== '') echo '</div>';
        $currentTipe = $item['tipe_ruangan'];
        $label = StandarModel::tipeList()[$currentTipe] ?? $currentTipe;
        echo "<div class='mb-4'><div class='small fw-bold text-muted text-uppercase mb-2' style='border-left:3px solid #2d8653;padding-left:8px'>{$label}</div>";
      endif;
      $existing = $detailMap[$item['id']] ?? null;
      $kondisi  = $existing['kondisi'] ?? '';
      $catatan  = $existing['catatan'] ?? '';
      $bc       = match($kondisi){'bersih'=>'success','kurang_bersih'=>'warning','kotor'=>'danger',default=>''};
    ?>
    <div class="border rounded-3 mb-2 p-3 <?= $bc ? "border-{$bc}" : '' ?>" id="ei-<?= $item['id'] ?>">
      <div class="d-flex align-items-start gap-3">
        <span class="text-muted small mt-1" style="min-width:22px"><?= $i+1 ?>.</span>
        <div class="flex-grow-1">
          <div class="fw-semibold mb-2 small"><?= Helper::e($item['aspek']) ?></div>
          <div class="d-flex gap-2 flex-wrap mb-2">
            <?php foreach (['bersih','kurang_bersih','kotor'] as $k):
              $kl = match($k){'bersih'=>'Bersih','kurang_bersih'=>'Kurang Bersih','kotor'=>'Kotor'};
              $checked = $kondisi === $k ? 'checked' : '';
            ?>
            <label class="d-flex align-items-center gap-1 px-3 py-1 rounded-pill border small"
                   style="cursor:pointer" onclick="toggleCatatan(<?= $item['id'] ?>, '<?= $k ?>')">
              <input type="radio" name="kondisi[<?= $item['id'] ?>]" value="<?= $k ?>" <?= $checked ?> required>
              <?= $kl ?>
            </label>
            <?php endforeach; ?>
          </div>
          <div class="catatan-<?= $item['id'] ?> <?= (!$kondisi || $kondisi==='bersih') ? 'd-none' : '' ?>">
            <textarea name="catatan[<?= $item['id'] ?>]" class="form-control form-control-sm"
                      rows="2" placeholder="Catatan..."><?= Helper::e($catatan) ?></textarea>
          </div>
          <!-- Foto per item checklist -->
          <?php $fotoFile = $existing['foto'] ?? null; $iid = $inspeksi['id']; $sid = $item['id']; $fuid = "fu_{$iid}_{$sid}"; ?>
          <div class="foto-area mt-2">
            <div class="foto-upload-wrap" id="<?= $fuid ?>">
              <?php if ($fotoFile): ?>
              <div id="<?= $fuid ?>_preview" class="d-flex align-items-center gap-2 mb-1">
                <img src="<?= Helper::uploadUrl("inspeksi/{$iid}/{$fotoFile}") ?>" class="rounded border foto-thumb"
                     style="width:56px;height:56px;object-fit:cover;cursor:pointer"
                     onclick="openGallery('<?= Helper::uploadUrl("inspeksi/{$iid}/{$fotoFile}") ?>')">
                <div>
                  <div class="small text-muted" style="font-size:11px"><?= Helper::e($fotoFile) ?></div>
                  <button type="button" class="btn btn-sm btn-outline-danger mt-1" style="font-size:11px;padding:2px 8px"
                    onclick="deleteFoto('<?= $fuid ?>',<?= $iid ?>,<?= $sid ?>,'<?= Helper::e($fotoFile) ?>')">
                    <i class="bi bi-trash"></i> Hapus
                  </button>
                </div>
              </div>
              <?php endif; ?>
              <div id="<?= $fuid ?>_input">
                <label class="foto-upload-label" for="<?= $fuid ?>_file">
                  <i class="bi bi-camera"></i>
                  <span id="<?= $fuid ?>_label"><?= $fotoFile ? 'Ganti Foto' : 'Tambah Foto' ?></span>
                </label>
                <input type="file" id="<?= $fuid ?>_file" accept="image/*" capture="environment"
                       class="d-none" data-uid="<?= $fuid ?>"
                       data-inspeksi-id="<?= $iid ?>" data-standar-id="<?= $sid ?>"
                       onchange="handleFotoChange(this)">
              </div>
              <div id="<?= $fuid ?>_progress" class="d-none mt-1">
                <div class="progress" style="height:4px">
                  <div class="progress-bar bg-success progress-bar-striped progress-bar-animated w-100"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; if ($currentTipe) echo '</div>'; ?>

    <div class="mb-3">
      <label class="form-label fw-semibold">Catatan Umum</label>
      <textarea name="catatan_umum" class="form-control" rows="3"><?= Helper::e($inspeksi['catatan_umum'] ?? '') ?></textarea>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" name="is_draft" value="0" class="btn btn-success">
        <i class="bi bi-check-circle me-1"></i>Simpan Final
      </button>
      <button type="submit" name="is_draft" value="1" class="btn btn-outline-secondary">
        <i class="bi bi-save me-1"></i>Simpan Draft
      </button>
    </div>
  </form>
</div>

<script>
function toggleCatatan(id, kondisi) {
  const wrap = document.querySelector('.catatan-' + id);
  if (wrap) wrap.classList.toggle('d-none', kondisi === 'bersih');
}
</script>
