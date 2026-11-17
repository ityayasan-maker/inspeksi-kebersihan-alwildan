<div class="page-card" style="max-width:620px">
  <div class="page-card-header">
    <h5><i class="bi bi-list-check me-2 text-success"></i><?= Helper::e($pageTitle) ?></h5>
    <a href="<?= Helper::baseUrl('standar') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
  </div>
  <form method="POST" action="<?= Helper::baseUrl(isset($standar) ? "standar/edit/{$standar['id']}" : 'standar/create') ?>">
    <?= Helper::csrfField() ?>
    <div class="mb-3">
      <label class="form-label fw-semibold">Tipe Ruangan <span class="text-danger">*</span></label>
      <select name="tipe_ruangan" class="form-select" required>
        <option value="">-- Pilih Tipe --</option>
        <?php foreach ($tipeList as $v => $l): ?>
          <option value="<?= $v ?>" <?= (($standar['tipe_ruangan']??'') === $v) ? 'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label fw-semibold">Aspek Kebersihan <span class="text-danger">*</span></label>
      <input type="text" name="aspek" class="form-control" placeholder="cth: Lantai" maxlength="200"
             value="<?= Helper::e($standar['aspek'] ?? '') ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label fw-semibold">Standar Minimal</label>
      <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi standar yang harus dipenuhi..."><?= Helper::e($standar['deskripsi'] ?? '') ?></textarea>
    </div>
    <div class="mb-4">
      <label class="form-label fw-semibold">Frekuensi <span class="text-danger">*</span></label>
      <select name="frekuensi" class="form-select" required>
        <?php foreach ($frekList as $v => $l): ?>
          <option value="<?= $v ?>" <?= (($standar['frekuensi']??'harian') === $v) ? 'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Simpan</button>
    <a href="<?= Helper::baseUrl('standar') ?>" class="btn btn-outline-secondary ms-2">Batal</a>
  </form>
</div>
