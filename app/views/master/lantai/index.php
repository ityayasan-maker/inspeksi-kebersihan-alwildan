<div class="row g-3">
  <!-- Form Tambah -->
  <div class="col-md-4">
    <div class="page-card">
      <div class="page-card-header"><h5><i class="bi bi-layers me-2 text-success"></i>Tambah Lantai</h5></div>
      <form method="POST" action="<?= Helper::baseUrl('lantai/create') ?>">
        <?= Helper::csrfField() ?>
        <div class="mb-3">
          <label class="form-label fw-semibold">Gedung <span class="text-danger">*</span></label>
          <select name="gedung_id" class="form-select" required>
            <option value="">-- Pilih Gedung --</option>
            <?php foreach ($gedung as $g): ?>
              <option value="<?= $g['id'] ?>">[<?= strtoupper($g['unit']) ?>] <?= Helper::e($g['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Nama Lantai <span class="text-danger">*</span></label>
          <input type="text" name="nama" class="form-control" placeholder="cth: Lantai 1" required>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold">Urutan</label>
          <input type="number" name="urutan" class="form-control" value="1" min="1" max="99">
        </div>
        <button type="submit" class="btn btn-success w-100"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
      </form>
    </div>
  </div>

  <!-- Tabel -->
  <div class="col-md-8">
    <div class="page-card">
      <div class="page-card-header"><h5><i class="bi bi-table me-2"></i>Daftar Lantai</h5></div>
      <div class="table-responsive">
        <table id="tblLantai" class="table table-hover align-middle" style="width:100%">
          <thead class="table-light">
            <tr><th>Gedung</th><th>Unit</th><th>Lantai</th><th class="text-center">Urutan</th><th>Ruangan</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php foreach ($lantai as $l): ?>
            <tr>
              <td class="fw-semibold small"><?= Helper::e($l['gedung_nama']) ?></td>
              <td><span class="badge bg-<?= match($l['gedung_unit']){'smp'=>'success','smk'=>'primary','yayasan'=>'warning','masjid'=>'info',default=>'secondary'} ?>"><?= strtoupper($l['gedung_unit']) ?></span></td>
              <td><?= Helper::e($l['nama']) ?></td>
              <td class="text-center"><?= $l['urutan'] ?></td>
              <td class="text-center"><?= $l['total_ruangan'] ?></td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditLantai"
                  data-id="<?= $l['id'] ?>" data-nama="<?= Helper::e($l['nama']) ?>" data-urutan="<?= $l['urutan'] ?>">
                  <i class="bi bi-pencil"></i>
                </button>
                <form id="del-l<?= $l['id'] ?>" method="POST" action="<?= Helper::baseUrl("lantai/delete") ?>" class="d-inline">
                  <?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= $l['id'] ?>">
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('del-l<?= $l['id'] ?>')"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEditLantai" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="POST" id="formEditLantai">
        <?= Helper::csrfField() ?>
        <div class="modal-header"><h6 class="modal-title">Edit Lantai</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label fw-semibold">Nama</label><input type="text" name="nama" id="editNama" class="form-control" required></div>
          <div class="mb-2"><label class="form-label fw-semibold">Urutan</label><input type="number" name="urutan" id="editUrutan" class="form-control" min="1"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success btn-sm">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$('#tblLantai').DataTable({columnDefs:[{orderable:false,targets:-1}]});
document.getElementById('modalEditLantai').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('formEditLantai').action = '<?= Helper::baseUrl('lantai/edit/') ?>' + btn.dataset.id;
  document.getElementById('editNama').value   = btn.dataset.nama;
  document.getElementById('editUrutan').value = btn.dataset.urutan;
});
</script>
