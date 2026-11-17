<form method="POST" action="<?= Helper::baseUrl('settings') ?>">
  <?= Helper::csrfField() ?>

  <!-- Tab nav -->
  <ul class="nav nav-tabs mb-3" id="settingsTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabGeneral"><i class="bi bi-gear me-1"></i>Umum</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabScoring"><i class="bi bi-123 me-1"></i>Scoring</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabEmail"><i class="bi bi-envelope me-1"></i>Email / SMTP</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabUpload"><i class="bi bi-upload me-1"></i>Upload</a></li>
  </ul>

  <div class="tab-content">

    <!-- General -->
    <div class="tab-pane fade show active" id="tabGeneral">
      <div class="page-card">
        <div class="page-card-header"><h6 class="mb-0 fw-bold">Pengaturan Umum</h6></div>
        <?php foreach ($groups['general'] as $s): ?>
          <div class="mb-3">
            <label class="form-label fw-semibold small"><?= Helper::e($s['label'] ?? $s['key_name']) ?></label>
            <input type="text" name="settings[<?= Helper::e($s['key_name']) ?>]"
                   class="form-control form-control-sm" value="<?= Helper::e($s['value'] ?? '') ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Scoring -->
    <div class="tab-pane fade" id="tabScoring">
      <div class="page-card">
        <div class="page-card-header"><h6 class="mb-0 fw-bold">Konfigurasi Scoring</h6></div>
        <div class="alert alert-info small mb-3">
          <i class="bi bi-info-circle me-1"></i>
          Skor menentukan status inspeksi. Default: Bersih=100, Kurang Bersih=70, Kotor=30.
        </div>
        <?php foreach ($groups['scoring'] as $s): ?>
          <div class="mb-3">
            <label class="form-label fw-semibold small"><?= Helper::e($s['label'] ?? $s['key_name']) ?></label>
            <input type="number" name="settings[<?= Helper::e($s['key_name']) ?>]"
                   class="form-control form-control-sm" style="max-width:140px"
                   value="<?= Helper::e($s['value'] ?? '') ?>" min="0" max="100">
            <?php if ($s['key_name'] === 'threshold_alert'): ?>
              <div class="form-text">Inspeksi dengan skor ≤ nilai ini akan memicu notifikasi & email alert.</div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Email / SMTP -->
    <div class="tab-pane fade" id="tabEmail">
      <div class="page-card">
        <div class="page-card-header">
          <h6 class="mb-0 fw-bold">Konfigurasi Email SMTP</h6>
        </div>
        <div class="alert alert-info small mb-3">
          <i class="bi bi-info-circle me-1"></i>
          Untuk Hostinger: Host = <code>mail.hostinger.com</code>, Port = <code>465</code> (SSL).
          Username = alamat email lengkap.
        </div>
        <?php foreach ($groups['email'] as $s): ?>
          <div class="mb-3">
            <label class="form-label fw-semibold small"><?= Helper::e($s['label'] ?? $s['key_name']) ?></label>
            <?php if ($s['key_name'] === 'smtp_pass'): ?>
              <input type="password" name="settings[<?= Helper::e($s['key_name']) ?>]"
                     class="form-control form-control-sm" placeholder="Kosongkan jika tidak ganti"
                     autocomplete="new-password">
              <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
            <?php elseif ($s['key_name'] === 'smtp_port'): ?>
              <select name="settings[<?= Helper::e($s['key_name']) ?>]" class="form-select form-select-sm" style="max-width:140px">
                <option value="465" <?= $s['value']==='465'?'selected':'' ?>>465 (SSL)</option>
                <option value="587" <?= $s['value']==='587'?'selected':'' ?>>587 (TLS)</option>
                <option value="25"  <?= $s['value']==='25'?'selected':'' ?>>25 (Plain)</option>
              </select>
            <?php else: ?>
              <input type="text" name="settings[<?= Helper::e($s['key_name']) ?>]"
                     class="form-control form-control-sm" value="<?= Helper::e($s['value'] ?? '') ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <!-- Test Email -->
        <div class="mt-4 pt-3 border-top">
          <h6 class="fw-semibold mb-2">Test Koneksi Email</h6>
          <p class="small text-muted">Simpan pengaturan terlebih dahulu, lalu klik tombol test untuk mengirim email percobaan ke akun Anda.</p>
          <button type="submit" name="test_email" value="1" class="btn btn-outline-info btn-sm">
            <i class="bi bi-send me-1"></i>Kirim Test Email
          </button>
        </div>
      </div>
    </div>

    <!-- Upload -->
    <div class="tab-pane fade" id="tabUpload">
      <div class="page-card">
        <div class="page-card-header"><h6 class="mb-0 fw-bold">Konfigurasi Upload</h6></div>
        <?php foreach ($groups['upload'] as $s): ?>
          <div class="mb-3">
            <label class="form-label fw-semibold small"><?= Helper::e($s['label'] ?? $s['key_name']) ?></label>
            <input type="text" name="settings[<?= Helper::e($s['key_name']) ?>]"
                   class="form-control form-control-sm" value="<?= Helper::e($s['value'] ?? '') ?>">
            <?php if ($s['key_name']==='upload_max_size'): ?>
              <div class="form-text">Dalam satuan KB. Default: 2048 (2 MB).</div>
            <?php elseif ($s['key_name']==='upload_allowed'): ?>
              <div class="form-text">Pisahkan dengan koma. Contoh: jpg,jpeg,png,webp</div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div><!-- /tab-content -->

  <div class="mt-3">
    <button type="submit" class="btn btn-success">
      <i class="bi bi-check-lg me-1"></i>Simpan Pengaturan
    </button>
  </div>
</form>
