<div class="page-card" style="max-width:600px">
  <div class="page-card-header">
    <h5><i class="bi bi-buildings me-2 text-success"></i><?= Helper::e($pageTitle) ?></h5>
    <a href="<?= Helper::baseUrl('gedung') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
  </div>
  <form method="POST" action="<?= Helper::baseUrl(isset($gedung) ? "gedung/edit/{$gedung['id']}" : 'gedung/create') ?>">
    <?= Helper::csrfField() ?>
    <div class="mb-3">
      <label class="form-label fw-semibold">Kode Gedung <span class="text-danger">*</span></label>
      <input type="text" name="kode" class="form-control" placeholder="cth: GD-SMK-A" maxlength="20"
             value="<?= Helper::e($gedung['kode'] ?? '') ?>" required>
      <div class="form-text">Huruf kapital, unik. Contoh: GD-SMP, GD-MSJ</div>
    </div>
    <div class="mb-3">
      <label class="form-label fw-semibold">Nama Gedung <span class="text-danger">*</span></label>
      <input type="text" name="nama" class="form-control" placeholder="cth: Gedung Utama Al Wildan"
             value="<?= Helper::e($gedung['nama'] ?? '') ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
      <select name="unit" class="form-select" required>
        <option value="">-- Pilih Unit --</option>
        <?php foreach (Helper::unitList() as $v => $l): ?>
          <option value="<?= $v ?>" <?= (($gedung['unit'] ?? '') === $v) ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">Pilih unit sekolah Al Wildan, Yayasan, Masjid, dll.</div>
    </div>
    <div class="mb-4">
      <label class="form-label fw-semibold">Keterangan</label>
      <textarea name="keterangan" class="form-control" rows="3" placeholder="Keterangan tambahan..."><?= Helper::e($gedung['keterangan'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Simpan</button>
    <a href="<?= Helper::baseUrl('gedung') ?>" class="btn btn-outline-secondary ms-2">Batal</a>
  </form>
</div>
