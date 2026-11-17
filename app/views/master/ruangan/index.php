<div class="page-card">
  <div class="page-card-header">
    <h5><i class="bi bi-door-open me-2 text-success"></i>Master Ruangan</h5>
    <a href="<?= Helper::baseUrl('ruangan/create') ?>" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Ruangan</a>
  </div>
  <div class="table-responsive">
    <table id="tblRuangan" class="table table-hover align-middle" style="width:100%">
      <thead class="table-light">
        <tr><th>Kode</th><th>Nama Ruangan</th><th>Tipe</th><th>Gedung</th><th>Lantai</th><th>Petugas PJ</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php $tipeList = StandarModel::tipeList(); foreach ($ruangan as $r): ?>
        <tr>
          <td><span class="badge bg-secondary"><?= Helper::e($r['kode']) ?></span></td>
          <td class="fw-semibold"><?= Helper::e($r['nama']) ?></td>
          <td><span class="badge bg-light text-dark border"><?= Helper::e($tipeList[$r['tipe']] ?? $r['tipe']) ?></span></td>
          <td class="small"><?= Helper::e($r['gedung_nama']) ?></td>
          <td class="small"><?= Helper::e($r['lantai_nama']) ?></td>
          <td class="small"><?= $r['petugas_nama'] ? Helper::e($r['petugas_nama']) : '<span class="text-muted">—</span>' ?></td>
          <td>
            <a href="<?= Helper::baseUrl("checklist/{$r['id']}") ?>" class="btn btn-sm btn-outline-success me-1" title="Kelola Checklist"><i class="bi bi-list-check"></i></a>
            <a href="<?= Helper::baseUrl("ruangan/edit/{$r['id']}") ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
            <form id="del-r<?= $r['id'] ?>" method="POST" action="<?= Helper::baseUrl('ruangan/delete') ?>" class="d-inline">
              <?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('del-r<?= $r['id'] ?>')"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<script>$('#tblRuangan').DataTable({columnDefs:[{orderable:false,targets:-1}]});</script>
