<div class="page-card" style="max-width:680px">
  <div class="page-card-header">
    <h5><i class="bi bi-door-open me-2 text-success"></i><?= Helper::e($pageTitle) ?></h5>
    <a href="<?= Helper::baseUrl('ruangan') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
  </div>
  <form method="POST" action="<?= Helper::baseUrl(isset($ruangan) ? "ruangan/edit/{$ruangan['id']}" : 'ruangan/create') ?>">
    <?= Helper::csrfField() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Gedung <span class="text-danger">*</span></label>
        <select name="gedung_id_sel" id="selGedung" class="form-select" required>
          <option value="">-- Pilih Gedung --</option>
          <?php foreach ($gedung as $g): ?>
            <option value="<?= $g['id'] ?>" <?= (($ruangan['gedung_id']??0) == $g['id']) ? 'selected':'' ?>>[<?= strtoupper($g['unit']) ?>] <?= Helper::e($g['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Lantai <span class="text-danger">*</span></label>
        <select name="lantai_id" id="selLantai" class="form-select" required>
          <option value="">-- Pilih Lantai --</option>
          <?php if (!empty($lantai)) foreach ($lantai as $l): ?>
            <option value="<?= $l['id'] ?>" <?= (($ruangan['lantai_id']??0) == $l['id']) ? 'selected':'' ?>><?= Helper::e($l['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Kode <span class="text-danger">*</span></label>
        <input type="text" name="kode" class="form-control text-uppercase" placeholder="SMP-KLS" maxlength="30"
               value="<?= Helper::e($ruangan['kode'] ?? '') ?>" required>
      </div>
      <div class="col-md-8">
        <label class="form-label fw-semibold">Nama Ruangan <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control" placeholder="cth: Ruang Kelas 7A"
               value="<?= Helper::e($ruangan['nama'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Tipe Ruangan <span class="text-danger">*</span></label>
        <select name="tipe" class="form-select" required>
          <?php foreach ($tipeList as $v => $l): ?>
            <option value="<?= $v ?>" <?= (($ruangan['tipe']??'') === $v) ? 'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Zona</label>
        <input type="text" name="zona" class="form-control" placeholder="cth: Area SMK"
               value="<?= Helper::e($ruangan['zona'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Petugas Penanggung Jawab</label>
        <select name="assigned_user_id" class="form-select">
          <option value="">— Tidak ada —</option>
          <?php foreach ($petugas as $p): ?>
            <option value="<?= $p['id'] ?>" <?= (($ruangan['assigned_user_id']??0) == $p['id']) ? 'selected':'' ?>><?= Helper::e($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Luas (m²)</label>
        <input type="number" name="luas_m2" class="form-control" step="0.01" placeholder="0.00"
               value="<?= $ruangan['luas_m2'] ?? '' ?>">
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold">Keterangan</label>
        <textarea name="keterangan" class="form-control" rows="2"><?= Helper::e($ruangan['keterangan'] ?? '') ?></textarea>
      </div>
    </div>
    <div class="mt-4">
      <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Simpan</button>
      <a href="<?= Helper::baseUrl('ruangan') ?>" class="btn btn-outline-secondary ms-2">Batal</a>
    </div>
  </form>
</div>
<script>
// Load lantai by gedung (AJAX)
document.getElementById('selGedung').addEventListener('change', function() {
  const gedungId = this.value;
  const sel = document.getElementById('selLantai');
  sel.innerHTML = '<option value="">Memuat...</option>';
  if (!gedungId) { sel.innerHTML = '<option value="">-- Pilih Lantai --</option>'; return; }
  fetch('<?= Helper::baseUrl('lantai/by-gedung') ?>?gedung_id=' + gedungId + '&ajax=1', {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r => r.json()).then(data => {
      sel.innerHTML = '<option value="">-- Pilih Lantai --</option>';
      data.forEach(l => sel.innerHTML += `<option value="${l.id}">${l.nama}</option>`);
    });
});
</script>
