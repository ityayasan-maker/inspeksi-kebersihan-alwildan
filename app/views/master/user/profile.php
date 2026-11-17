<div class="page-card" style="max-width:520px">
  <div class="page-card-header">
    <h5><i class="bi bi-person-circle me-2 text-success"></i>Profil Saya</h5>
  </div>
  <form method="POST" action="<?= Helper::baseUrl('users/profile') ?>">
    <?= Helper::csrfField() ?>
    <div class="mb-3">
      <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
      <input type="text" name="name" class="form-control" value="<?= Helper::e($user['name']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label fw-semibold">Email</label>
      <input type="email" class="form-control" value="<?= Helper::e($user['email']) ?>" disabled>
    </div>
    <div class="mb-4">
      <label class="form-label fw-semibold">No. HP</label>
      <input type="text" name="phone" class="form-control" value="<?= Helper::e($user['phone']??'') ?>">
    </div>
    <hr>
    <p class="fw-semibold mb-2">Ganti Password <span class="text-muted fw-normal small">(opsional)</span></p>
    <div class="mb-3">
      <label class="form-label">Password Lama</label>
      <input type="password" name="current_password" class="form-control" autocomplete="current-password">
    </div>
    <div class="mb-4">
      <label class="form-label">Password Baru <span class="text-muted small">(min. 8 karakter)</span></label>
      <input type="password" name="new_password" class="form-control" autocomplete="new-password" minlength="8">
    </div>
    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Simpan Perubahan</button>
  </form>
</div>
