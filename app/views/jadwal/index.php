<?php
/**
 * View Jadwal Foto Kebersihan Karyawan Al-Wildan 8 Kemang
 * Mobile First, Realtime Response, zero-delay
 */
$isPetugas = ($role === 'petugas');
$currentHour = (int)date('H');
?>

<div class="container-fluid px-0 px-md-3">
  <!-- ── Page Header ── -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h4 class="fw-bold mb-1" style="color:var(--blue-navy,#0f2744)">
        <i class="bi bi-calendar-check me-2 text-primary"></i>Jadwal Foto Kebersihan
      </h4>
      <p class="text-muted small mb-0">
        Jadwal foto wajib harian staf kebersihan Al-Wildan 8: <strong>Sebelum Jam 09:00 Pagi</strong> &amp; <strong>Sebelum Jam 14:00 Siang</strong>.
      </p>
    </div>
    <div class="d-flex gap-2">
      <?php if (in_array($role, ['admin', 'supervisor'])): ?>
      <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
        <i class="bi bi-plus-circle me-1"></i>Tambah Jadwal
      </button>
      <?php endif; ?>
      <a href="<?= Helper::baseUrl('jadwal?date=' . date('Y-m-d')) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-clockwise me-1"></i>Hari Ini
      </a>
    </div>
  </div>

  <!-- ── Summary Cards (Pagi & Siang) ── -->
  <div class="row g-2 g-md-3 mb-3">
    <!-- Sesi Pagi Card -->
    <div class="col-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100" style="background:linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-left: 4px solid #f97316 !important;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-warning text-dark fw-bold" style="font-size:11px">
              <i class="bi bi-sun-fill me-1 text-warning"></i>Sesi Pagi
            </span>
            <small class="text-muted fw-semibold">&lt; 09:00 WIB</small>
          </div>
          <div class="d-flex align-items-baseline justify-content-between">
            <h3 class="fw-bold mb-0 text-dark"><?= $summary['pagi_done'] ?><span class="fs-6 fw-normal text-muted">/<?= $summary['pagi_total'] ?></span></h3>
            <span class="badge bg-white text-dark shadow-xs border"><?= $summary['pagi_persen'] ?>%</span>
          </div>
          <div class="progress mt-2" style="height:6px; background:#fed7aa">
            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $summary['pagi_persen'] ?>%"></div>
          </div>
          <div class="d-flex justify-content-between mt-2" style="font-size:10.5px">
            <span class="text-success"><i class="bi bi-check-circle me-1"></i><?= $summary['pagi_tepat'] ?> Tepat</span>
            <span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i><?= $summary['pagi_telat'] ?> Terlambat</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Sesi Siang Card -->
    <div class="col-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100" style="background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 4px solid #3b82f6 !important;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-primary fw-bold" style="font-size:11px">
              <i class="bi bi-brightness-high-fill me-1"></i>Sesi Siang
            </span>
            <small class="text-muted fw-semibold">&lt; 14:00 WIB</small>
          </div>
          <div class="d-flex align-items-baseline justify-content-between">
            <h3 class="fw-bold mb-0 text-dark"><?= $summary['siang_done'] ?><span class="fs-6 fw-normal text-muted">/<?= $summary['siang_total'] ?></span></h3>
            <span class="badge bg-white text-dark shadow-xs border"><?= $summary['siang_persen'] ?>%</span>
          </div>
          <div class="progress mt-2" style="height:6px; background:#bfdbfe">
            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $summary['siang_persen'] ?>%"></div>
          </div>
          <div class="d-flex justify-content-between mt-2" style="font-size:10.5px">
            <span class="text-success"><i class="bi bi-check-circle me-1"></i><?= $summary['siang_tepat'] ?> Tepat</span>
            <span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i><?= $summary['siang_telat'] ?> Terlambat</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Selesai Card -->
    <div class="col-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-left: 4px solid #10b981 !important;">
        <div class="card-body p-3">
          <span class="text-muted small fw-semibold d-block mb-1">Total Foto Selesai</span>
          <div class="d-flex align-items-baseline justify-content-between">
            <h3 class="fw-bold mb-0 text-success"><?= $summary['done'] ?><span class="fs-6 fw-normal text-muted">/<?= $summary['total'] ?></span></h3>
            <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $summary['total_persen'] ?>%</span>
          </div>
          <div class="progress mt-2" style="height:6px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $summary['total_persen'] ?>%"></div>
          </div>
          <small class="text-muted mt-2 d-block" style="font-size:11px">Dari seluruh jadwal ruangan hari ini</small>
        </div>
      </div>
    </div>

    <!-- Status Jam Sekarang -->
    <div class="col-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-left: 4px solid #6366f1 !important;">
        <div class="card-body p-3">
          <span class="text-muted small fw-semibold d-block mb-1">Target Berjalan</span>
          <div class="d-flex align-items-center gap-2 mb-1">
            <?php if ($currentHour < 9): ?>
              <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Sesi Pagi</span>
              <span class="text-dark fw-bold small">Maks 09:00</span>
            <?php elseif ($currentHour < 14): ?>
              <span class="badge bg-primary"><i class="bi bi-clock me-1"></i>Sesi Siang</span>
              <span class="text-dark fw-bold small">Maks 14:00</span>
            <?php else: ?>
              <span class="badge bg-secondary"><i class="bi bi-check2-all me-1"></i>Selesai</span>
              <span class="text-dark fw-bold small">Evaluasi Harian</span>
            <?php endif; ?>
          </div>
          <p class="text-muted mb-0" style="font-size:11px">
            <?= date('d M Y') ?> &bull; <span id="liveClock" class="fw-semibold text-dark"><?= date('H:i') ?> WIB</span>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Filter & Search Bar ── -->
  <div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
      <form method="GET" action="<?= Helper::baseUrl('jadwal') ?>" class="row g-2 align-items-end">
        <div class="col-6 col-md-3">
          <label class="form-label small fw-semibold text-muted mb-1">Tanggal</label>
          <input type="date" name="date" class="form-control form-control-sm rounded-3" value="<?= Helper::e($date) ?>" onchange="this.form.submit()">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small fw-semibold text-muted mb-1">Sesi</label>
          <select name="sesi" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
            <option value="">Semua Sesi</option>
            <option value="pagi" <?= $sesi === 'pagi' ? 'selected' : '' ?>>Pagi (&lt; 09:00)</option>
            <option value="siang" <?= $sesi === 'siang' ? 'selected' : '' ?>>Siang (&lt; 14:00)</option>
          </select>
        </div>
        <?php if (!$isPetugas): ?>
        <div class="col-12 col-md-4">
          <label class="form-label small fw-semibold text-muted mb-1">Staf Kebersihan</label>
          <select name="petugas_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
            <option value="">Semua Staf (8 Petugas)</option>
            <?php foreach ($petugasList as $p): ?>
              <option value="<?= $p['id'] ?>" <?= $petugasId == $p['id'] ? 'selected' : '' ?>>
                <?= Helper::e($p['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php else: ?>
        <input type="hidden" name="petugas_id" value="<?= $userId ?>">
        <?php endif; ?>
        <div class="col-12 col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm rounded-3 w-100">
            <i class="bi bi-funnel me-1"></i>Filter
          </button>
          <a href="<?= Helper::baseUrl('jadwal') ?>" class="btn btn-outline-secondary btn-sm rounded-3 px-3" title="Reset">
            <i class="bi bi-x-lg"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- ── List Jadwal Foto Kebersihan ── -->
  <?php if (empty($jadwalList)): ?>
    <div class="card border-0 shadow-sm rounded-4 text-center py-5">
      <div class="card-body">
        <i class="bi bi-calendar-x text-muted" style="font-size:42px"></i>
        <h6 class="fw-bold mt-2 text-dark">Tidak ada jadwal kebersihan</h6>
        <p class="text-muted small mb-0">Silakan sesuaikan filter atau tambahkan penugasan jadwal baru.</p>
      </div>
    </div>
  <?php else: ?>
    <!-- ── MOBILE VIEW: Card List (Ultra Responsive & Touch Friendly) ── -->
    <div class="d-md-none">
      <div class="d-flex justify-content-between align-items-center mb-2 px-1">
        <span class="text-muted small fw-semibold">Menampilkan <?= count($jadwalList) ?> jadwal</span>
        <span class="badge bg-light text-muted border"><?= Helper::tanggal($date) ?></span>
      </div>

      <div class="row g-2">
        <?php foreach ($jadwalList as $j): ?>
          <?php
            $isDone = !empty($j['inspeksi_id']);
            $isPagi = ($j['sesi'] === 'pagi');
            $badgeBg = match($j['kepatuhan']) {
              'tepat_waktu' => 'success',
              'terlambat'   => 'warning text-dark',
              'belum_foto'  => 'danger',
              default       => 'primary'
            };
          ?>
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="border-left: 5px solid <?= $isDone ? '#10b981' : ($j['kepatuhan'] === 'belum_foto' ? '#ef4444' : '#3b82f6') ?> !important;">
              <div class="card-body p-3">
                <!-- Baris Atas: Sesi & Status Kepatuhan -->
                <div class="d-flex align-items-center justify-content-between gap-1 mb-2">
                  <span class="badge <?= $isPagi ? 'bg-warning text-dark' : 'bg-primary' ?>" style="font-size:11px">
                    <i class="bi <?= $isPagi ? 'bi-sun-fill' : 'bi-brightness-high-fill' ?> me-1"></i>
                    Sesi <?= ucfirst($j['sesi']) ?> (&lt; <?= substr($j['jam_batas'], 0, 5) ?>)
                  </span>
                  <span class="badge bg-<?= $badgeBg ?>" style="font-size:10.5px">
                    <i class="bi <?= $isDone ? 'bi-check-circle-fill' : ($j['kepatuhan'] === 'belum_foto' ? 'bi-exclamation-triangle-fill' : 'bi-clock-fill') ?> me-1"></i>
                    <?= $j['kepatuhan_label'] ?>
                  </span>
                </div>

                <!-- Info Ruangan & Lokasi -->
                <h6 class="fw-bold mb-1 text-dark" style="font-size:15px">
                  <?= Helper::e($j['ruangan_nama']) ?>
                </h6>
                <div class="text-muted small mb-2" style="font-size:12px">
                  <i class="bi bi-geo-alt me-1 text-danger"></i><?= Helper::e($j['gedung_nama']) ?> &bull; <?= Helper::e($j['lantai_nama']) ?>
                </div>

                <!-- Info Petugas -->
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold" style="width:30px; height:30px; font-size:12px">
                      <i class="bi bi-person"></i>
                    </div>
                    <div>
                      <div class="fw-semibold text-dark" style="font-size:12.5px"><?= Helper::e($j['petugas_nama']) ?></div>
                      <div class="text-muted" style="font-size:11px">PJ Kebersihan</div>
                    </div>
                  </div>

                  <!-- Action Buttons Mobile -->
                  <div>
                    <?php if ($isDone): ?>
                      <div class="d-flex gap-1">
                        <?php if (!empty($j['foto_ruangan'])): ?>
                          <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-2 py-1" style="font-size:11px" onclick="openGallery('<?= Helper::uploadUrl('inspeksi/' . $j['inspeksi_id'] . '/' . $j['foto_ruangan']) ?>', 'Foto <?= Helper::e($j['ruangan_nama']) ?>')">
                            <i class="bi bi-image me-1"></i>Foto
                          </button>
                        <?php endif; ?>
                        <a href="<?= Helper::baseUrl('inspeksi/detail/' . $j['inspeksi_id']) ?>" class="btn btn-light btn-sm rounded-pill px-2 py-1 border" style="font-size:11px">
                          <i class="bi bi-eye"></i> Detail
                        </a>
                      </div>
                    <?php else: ?>
                      <a href="<?= Helper::baseUrl('inspeksi/create?ruangan_id=' . $j['ruangan_id'] . '&sesi=' . $j['sesi']) ?>" class="btn btn-success btn-sm rounded-pill px-3 py-1 fw-semibold shadow-xs" style="font-size:12px">
                        <i class="bi bi-camera me-1"></i>Foto Sekarang
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ── DESKTOP VIEW: Clean Table ── -->
    <div class="d-none d-md-block">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr style="font-size:12.5px">
                <th class="ps-3">Sesi / Batas Waktu</th>
                <th>Ruangan &amp; Lokasi</th>
                <th>Petugas PJ</th>
                <th>Status Kepatuhan</th>
                <th>Waktu Foto</th>
                <th>Skor</th>
                <th class="text-end pe-3">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($jadwalList as $j): ?>
                <?php
                  $isDone = !empty($j['inspeksi_id']);
                  $isPagi = ($j['sesi'] === 'pagi');
                  $badgeBg = match($j['kepatuhan']) {
                    'tepat_waktu' => 'success',
                    'terlambat'   => 'warning text-dark',
                    'belum_foto'  => 'danger',
                    default       => 'primary'
                  };
                ?>
                <tr>
                  <td class="ps-3">
                    <span class="badge <?= $isPagi ? 'bg-warning text-dark' : 'bg-primary' ?> px-2 py-1">
                      <i class="bi <?= $isPagi ? 'bi-sun-fill' : 'bi-brightness-high-fill' ?> me-1"></i>
                      Sesi <?= ucfirst($j['sesi']) ?>
                    </span>
                    <div class="text-muted small mt-1">&lt; Jam <?= substr($j['jam_batas'], 0, 5) ?> WIB</div>
                  </td>
                  <td>
                    <div class="fw-bold text-dark"><?= Helper::e($j['ruangan_nama']) ?></div>
                    <div class="text-muted small"><?= Helper::e($j['gedung_nama']) ?> &bull; <?= Helper::e($j['lantai_nama']) ?></div>
                  </td>
                  <td>
                    <div class="fw-semibold text-dark"><?= Helper::e($j['petugas_nama']) ?></div>
                    <div class="text-muted small"><?= Helper::e($j['petugas_email']) ?></div>
                  </td>
                  <td>
                    <span class="badge bg-<?= $badgeBg ?> px-2 py-1">
                      <i class="bi <?= $isDone ? 'bi-check-circle-fill' : ($j['kepatuhan'] === 'belum_foto' ? 'bi-exclamation-triangle-fill' : 'bi-clock-fill') ?> me-1"></i>
                      <?= $j['kepatuhan_label'] ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($isDone): ?>
                      <span class="fw-semibold text-dark"><?= substr($j['waktu_inspeksi'], 0, 5) ?> WIB</span>
                    <?php else: ?>
                      <span class="text-muted">&mdash;</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($isDone): ?>
                      <?php $sc = (float)$j['skor_total']; $scColor = $sc >= 85 ? 'success' : ($sc >= 70 ? 'warning text-dark' : 'danger'); ?>
                      <span class="badge bg-<?= $scColor ?> fs-7"><?= number_format($sc, 1) ?></span>
                    <?php else: ?>
                      <span class="text-muted">&mdash;</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end pe-3">
                    <?php if ($isDone): ?>
                      <div class="btn-group btn-group-sm">
                        <?php if (!empty($j['foto_ruangan'])): ?>
                          <button type="button" class="btn btn-outline-info" title="Lihat Foto" onclick="openGallery('<?= Helper::uploadUrl('inspeksi/' . $j['inspeksi_id'] . '/' . $j['foto_ruangan']) ?>', 'Foto <?= Helper::e($j['ruangan_nama']) ?>')">
                            <i class="bi bi-image"></i>
                          </button>
                        <?php endif; ?>
                        <a href="<?= Helper::baseUrl('inspeksi/detail/' . $j['inspeksi_id']) ?>" class="btn btn-outline-primary" title="Detail Inspeksi">
                          <i class="bi bi-eye"></i> Detail
                        </a>
                      </div>
                    <?php else: ?>
                      <a href="<?= Helper::baseUrl('inspeksi/create?ruangan_id=' . $j['ruangan_id'] . '&sesi=' . $j['sesi']) ?>" class="btn btn-success btn-sm rounded-pill px-3 shadow-xs">
                        <i class="bi bi-camera me-1"></i>Ambil Foto
                      </a>
                    <?php endif; ?>

                    <?php if (in_array($role, ['admin', 'supervisor'])): ?>
                      <form method="POST" action="<?= Helper::baseUrl('jadwal/delete') ?>" class="d-inline ms-1" onsubmit="return confirm('Hapus penugasan jadwal ini?')">
                        <?= Helper::csrfField() ?>
                        <input type="hidden" name="id" value="<?= $j['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm border-0" title="Hapus Jadwal">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- ── Modal Tambah Jadwal (Admin & Supervisor) ── -->
<?php if (in_array($role, ['admin', 'supervisor'])): ?>
<div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-labelledby="modalTambahJadwalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="modalTambahJadwalLabel">
          <i class="bi bi-calendar-plus text-primary me-2"></i>Tambah Penugasan Jadwal
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="<?= Helper::baseUrl('jadwal/create') ?>">
        <?= Helper::csrfField() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Staf Kebersihan <span class="text-danger">*</span></label>
            <select name="petugas_id" class="form-select rounded-3" required>
              <option value="">-- Pilih Staf Kebersihan --</option>
              <?php foreach ($petugasList as $p): ?>
                <option value="<?= $p['id'] ?>"><?= Helper::e($p['name']) ?> (<?= Helper::e($p['email']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Ruangan / Area <span class="text-danger">*</span></label>
            <select name="ruangan_id" class="form-select rounded-3" required>
              <option value="">-- Pilih Ruangan --</option>
              <?php foreach ($ruanganList as $r): ?>
                <option value="<?= $r['id'] ?>"><?= Helper::e($r['nama']) ?> (<?= Helper::e($r['gedung_nama']) ?> - <?= Helper::e($r['lantai_nama']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small fw-semibold">Sesi Waktu <span class="text-danger">*</span></label>
              <select name="sesi" class="form-select rounded-3" id="modalSesiSelect" onchange="autoFillJamBatas(this.value)">
                <option value="pagi">Sesi Pagi</option>
                <option value="siang">Sesi Siang</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold">Batas Jam Foto <span class="text-danger">*</span></label>
              <input type="time" name="jam_batas" id="modalJamBatas" class="form-control rounded-3" value="09:00" required>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label small fw-semibold">Catatan Khusus (Opsional)</label>
            <textarea name="catatan" class="form-control rounded-3" rows="2" placeholder="Instruksi kebersihan khusus ruangan ini..."></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-save me-1"></i>Simpan Jadwal
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function autoFillJamBatas(sesi) {
  const jamInput = document.getElementById('modalJamBatas');
  if (sesi === 'pagi') {
    jamInput.value = '09:00';
  } else if (sesi === 'siang') {
    jamInput.value = '14:00';
  }
}
</script>
<?php endif; ?>

<!-- Include Galeri Modal jika belum ada -->
<?php require_once APP_PATH . '/views/partials/gallery_modal.php'; ?>
