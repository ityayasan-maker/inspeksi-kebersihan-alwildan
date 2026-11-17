<div class="page-card">
  <div class="page-card-header">
    <h5><i class="bi bi-list-check me-2 text-success"></i>Standar Kebersihan</h5>
    <a href="<?= Helper::baseUrl('standar/create') ?>" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Standar</a>
  </div>

  <!-- Filter tab per tipe -->
  <ul class="nav nav-pills mb-3 flex-wrap gap-1" id="standarTabs">
    <li class="nav-item">
      <a class="nav-link active py-1 px-3" href="#" data-tipe="all">Semua</a>
    </li>
    <?php foreach ($tipeList as $k => $v): ?>
      <?php if (isset($grouped[$k])): ?>
      <li class="nav-item">
        <a class="nav-link py-1 px-3" href="#" data-tipe="<?= $k ?>"><?= $v ?></a>
      </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>

  <div class="table-responsive">
    <table id="tblStandar" class="table table-hover align-middle" style="width:100%">
      <thead class="table-light">
        <tr><th>#</th><th>Tipe Ruangan</th><th>Aspek Kebersihan</th><th>Standar Minimal</th><th>Frekuensi</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php $no=1; foreach ($grouped as $tipe => $items): foreach ($items as $s): ?>
        <tr data-tipe="<?= $tipe ?>">
          <td class="text-muted small"><?= $no++ ?></td>
          <td><span class="badge bg-light text-dark border small"><?= Helper::e($tipeList[$tipe] ?? $tipe) ?></span></td>
          <td class="fw-semibold"><?= Helper::e($s['aspek']) ?></td>
          <td class="text-muted small"><?= Helper::e(Helper::truncate($s['deskripsi']??'',80)) ?></td>
          <td><span class="badge bg-info text-dark"><?= Helper::e(StandarModel::frekuensiList()[$s['frekuensi']] ?? $s['frekuensi']) ?></span></td>
          <td>
            <a href="<?= Helper::baseUrl("standar/edit/{$s['id']}") ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
            <form id="del-s<?= $s['id'] ?>" method="POST" action="<?= Helper::baseUrl('standar/delete') ?>" class="d-inline">
              <?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= $s['id'] ?>">
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('del-s<?= $s['id'] ?>')"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const dt = $('#tblStandar').DataTable({columnDefs:[{orderable:false,targets:-1}]});
document.querySelectorAll('#standarTabs .nav-link').forEach(tab => {
  tab.addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelectorAll('#standarTabs .nav-link').forEach(t => t.classList.remove('active'));
    this.classList.add('active');
    const tipe = this.dataset.tipe;
    dt.rows().every(function() {
      const row = this.node();
      row.style.display = (tipe === 'all' || row.dataset.tipe === tipe) ? '' : 'none';
    });
  });
});
</script>
