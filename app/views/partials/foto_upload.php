<?php
/**
 * Partial: foto_upload.php
 * Include: <?php include $this->capture('partials.foto_upload', ['inspeksiId'=>$id, 'standarId'=>$standarId, 'existingFoto'=>$foto]); ?>
 * Atau langsung pakai JS component di bawah untuk inline form inspeksi.
 *
 * Variabel yang diharapkan:
 *   $inspeksiId   - int
 *   $standarId    - int
 *   $existingFoto - string|null  (filename saja)
 */
$inspeksiId   = $inspeksiId ?? 0;
$standarId    = $standarId  ?? 0;
$existingFoto = $existingFoto ?? null;
$uploadUrl    = Helper::baseUrl('upload/inspeksi-foto');
$deleteUrl    = Helper::baseUrl('upload/delete');
$uid          = "fu_{$inspeksiId}_{$standarId}";
?>

<div class="foto-upload-wrap" id="<?= $uid ?>">
  <?php if ($existingFoto): ?>
    <!-- Preview existing -->
    <div class="d-flex align-items-center gap-2 mb-2" id="<?= $uid ?>_preview">
      <img src="<?= Helper::uploadUrl("inspeksi/{$inspeksiId}/{$existingFoto}") ?>"
           class="rounded border foto-thumb" style="width:64px;height:64px;object-fit:cover;cursor:pointer"
           onclick="openGallery('<?= Helper::uploadUrl("inspeksi/{$inspeksiId}/{$existingFoto}") ?>')">
      <div>
        <div class="small text-muted"><?= Helper::e($existingFoto) ?></div>
        <button type="button" class="btn btn-xs btn-outline-danger mt-1"
                onclick="deleteFoto('<?= $uid ?>', <?= $inspeksiId ?>, <?= $standarId ?>, '<?= Helper::e($existingFoto) ?>')">
          <i class="bi bi-trash" style="font-size:11px"></i> Hapus
        </button>
      </div>
    </div>
  <?php endif; ?>

  <!-- Input file -->
  <div id="<?= $uid ?>_input">
    <label class="foto-upload-label" for="<?= $uid ?>_file">
      <i class="bi bi-camera"></i>
      <span id="<?= $uid ?>_label"><?= $existingFoto ? 'Ganti Foto' : 'Tambah Foto' ?></span>
    </label>
    <input type="file" id="<?= $uid ?>_file" accept="image/jpeg,image/png,image/webp"
           class="d-none foto-file-input"
           data-uid="<?= $uid ?>"
           data-inspeksi-id="<?= $inspeksiId ?>"
           data-standar-id="<?= $standarId ?>"
           onchange="handleFotoChange(this)">
  </div>

  <!-- Upload progress -->
  <div id="<?= $uid ?>_progress" class="d-none mt-1">
    <div class="progress" style="height:4px">
      <div class="progress-bar bg-success progress-bar-striped progress-bar-animated w-100"></div>
    </div>
    <div class="small text-muted mt-1">Mengupload...</div>
  </div>
</div>
