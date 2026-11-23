<div class="page-card">
  <div class="page-card-header">
    <h5><i class="bi bi-people me-2 text-success"></i>Manajemen Pengguna</h5>
    <a href="<?= Helper::baseUrl('users/create') ?>" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Pengguna</a>
  </div>
  <div class="table-responsive">
    <table id="tblUsers" class="table table-hover align-middle" style="width:100%">
      <thead class="table-light">
        <tr><th>Nama</th><th>Email</th><th>Role</th><th>No. HP</th><th>Status</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td class="fw-semibold"><?= Helper::e($u['name']) ?></td>
          <td class="small text-muted"><?= Helper::e($u['email']) ?></td>
          <td><?php $rc=match($u['role']){'admin'=>'danger','supervisor'=>'primary','penilai'=>'info text-dark','petugas'=>'success',default=>'secondary'}; ?>
            <span class="badge bg-<?= $rc ?>"><?= $u['role'] === 'penilai' ? 'Penilai (Kepsek)' : ucfirst($u['role']) ?></span></td>
          <td class="small"><?= Helper::e($u['phone'] ?? '—') ?></td>
          <td><?= $u['is_active'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?></td>
          <td>
            <a href="<?= Helper::baseUrl("users/edit/{$u['id']}") ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
            <?php if ($u['id'] != Session::get('user_id')): ?>
            <form id="del-u<?= $u['id'] ?>" method="POST" action="<?= Helper::baseUrl('users/delete') ?>" class="d-inline">
              <?= Helper::csrfField() ?><input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('del-u<?= $u['id'] ?>')"><i class="bi bi-person-x"></i></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<script>$('#tblUsers').DataTable({columnDefs:[{orderable:false,targets:-1}]});</script>
