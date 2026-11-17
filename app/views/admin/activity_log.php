<div class="page-card">
  <div class="page-card-header">
    <h5 class="mb-0"><i class="bi bi-journal-text me-2 text-success"></i>Activity Log</h5>
    <span class="badge bg-secondary"><?= count($logs) ?> entri</span>
  </div>

  <!-- Filter -->
  <form method="GET" action="<?= Helper::baseUrl('admin/activity-log') ?>" class="row g-2 mb-3">
    <div class="col-sm-3">
      <select name="module" class="form-select form-select-sm">
        <option value="">Semua Modul</option>
        <?php foreach (['auth','inspeksi','gedung','lantai','ruangan','standar','users','checklist','upload','settings'] as $m): ?>
          <option value="<?= $m ?>" <?= ($filterModule===$m)?'selected':'' ?>><?= ucfirst($m) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-3">
      <select name="user_id" class="form-select form-select-sm">
        <option value="">Semua User</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= $u['id'] ?>" <?= ($filterUser==$u['id'])?'selected':'' ?>>
            <?= Helper::e($u['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-2 d-flex gap-2">
      <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
        <i class="bi bi-search me-1"></i>Filter
      </button>
      <a href="<?= Helper::baseUrl('admin/activity-log') ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
  </form>

  <div class="table-responsive">
    <table id="tblLog" class="table table-hover align-middle table-sm" style="width:100%">
      <thead class="table-light">
        <tr><th>Waktu</th><th>User</th><th>Role</th><th>Aksi</th><th>Modul</th><th>Target</th><th>IP</th><th>Detail</th></tr>
      </thead>
      <tbody>
      <?php foreach ($logs as $log):
        $actionClass = match($log['action']){
          'create'=>'success','update'=>'warning','delete'=>'danger',
          'login'=>'primary','logout'=>'secondary',default=>'light text-dark border'
        };
      ?>
        <tr>
          <td class="small text-muted" style="white-space:nowrap"><?= Helper::formatDatetime($log['created_at']) ?></td>
          <td class="small fw-semibold"><?= Helper::e($log['user_name'] ?? 'System') ?></td>
          <td><span class="badge bg-<?= match($log['user_role']??''){'admin'=>'danger','supervisor'=>'primary','petugas'=>'success',default=>'secondary'} ?>" style="font-size:10px"><?= ucfirst($log['user_role']??'—') ?></span></td>
          <td><span class="badge bg-<?= $actionClass ?>" style="font-size:10px"><?= Helper::e($log['action']) ?></span></td>
          <td class="small"><?= Helper::e($log['module']) ?></td>
          <td class="small text-muted"><?= $log['target_id'] ? '#'.$log['target_id'] : '—' ?></td>
          <td class="small text-muted" style="font-size:11px"><?= Helper::e($log['ip_address'] ?? '—') ?></td>
          <td class="small text-muted"><?= Helper::e(Helper::truncate($log['detail'] ?? '', 60)) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
$('#tblLog').DataTable({order:[[0,'desc']],pageLength:25,columnDefs:[{orderable:false,targets:-1}]});
</script>
