<div class="page-card" style="max-width:580px">
  <div class="page-card-header">
    <h5><i class="bi bi-person-gear me-2 text-success"></i><?= Helper::e($pageTitle) ?></h5>
    <a href="<?= Helper::baseUrl('users') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
  </div>
  <form method="POST" action="<?= Helper::baseUrl(isset($user) ? "users/edit/{$user['id']}" : 'users/create') ?>">
    <?= Helper::csrfField() ?>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="<?= Helper::e($user['name']??'') ?>" required>
      </div>
      <div class="col-md-7">
        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="<?= Helper::e($user['email']??'') ?>" required>
      </div>
      <div class="col-md-5">
        <label class="form-label fw-semibold">No. HP</label>
        <input type="text" name="phone" class="form-control" value="<?= Helper::e($user['phone']??'') ?>" placeholder="08xxxxxxxx">
      </div>
      <div class="col-md-5">
        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
        <select name="role" class="form-select" required>
          <?php foreach(['petugas'=>'Petugas','supervisor'=>'Supervisor','admin'=>'Admin'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= (($user['role']??'petugas') === $v) ? 'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-7">
        <label class="form-label fw-semibold">Password <?= isset($user) ? '<span class="text-muted fw-normal">(kosongkan jika tidak ganti)</span>' : '<span class="text-danger">*</span>' ?></label>
        <input type="password" name="password" class="form-control" <?= !isset($user) ? 'required' : '' ?> autocomplete="new-password" minlength="8">
        <div class="form-text">Minimal 8 karakter</div>
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold">Konfirmasi Password</label>
        <input type="password" name="password_confirm" class="form-control" autocomplete="new-password">
      </div>
    </div>
    <div class="mt-4">
      <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Simpan</button>
      <a href="<?= Helper::baseUrl('users') ?>" class="btn btn-outline-secondary ms-2">Batal</a>
    </div>
  </form>
</div>
