<div class="page-card">
  <div class="page-card-header">
    <h5><i class="bi bi-buildings me-2 text-success"></i>Master Gedung</h5>
    <a href="<?= Helper::baseUrl('gedung/create') ?>" class="btn btn-success btn-sm">
      <i class="bi bi-plus-lg me-1"></i>Tambah Gedung
    </a>
  </div>
  <div class="table-responsive">
    <table id="tblGedung" class="table table-hover align-middle" style="width:100%">
      <thead class="table-light">
        <tr><th>Kode</th><th>Nama Gedung</th><th>Unit</th><th class="text-center">Ruangan</th><th>Keterangan</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php foreach ($gedung as $g): ?>
        <tr>
          <td><span class="badge bg-secondary"><?= Helper::e($g['kode']) ?></span></td>
          <td class="fw-semibold"><?= Helper::e($g['nama']) ?></td>
          <td><?= Helper::unitBadge($g['unit']) ?></td>
          <td class="text-center"><?= $g['total_ruangan'] ?></td>
          <td class="text-muted small"><?= Helper::e(Helper::truncate($g['keterangan']??'',60)) ?></td>
          <td>
            <a href="<?= Helper::baseUrl("gedung/edit/{$g['id']}") ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
            <form id="del-g<?= $g['id'] ?>" method="POST" action="<?= Helper::baseUrl('gedung/delete') ?>" class="d-inline">
              <?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= $g['id'] ?>">
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('del-g<?= $g['id'] ?>')"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<script>$('#tblGedung').DataTable({columnDefs:[{orderable:false,targets:-1}]});</script>
