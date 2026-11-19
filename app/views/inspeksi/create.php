<div class="page-card pb-5">
  <!-- Header Halaman -->
  <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
    <div class="d-flex align-items-center gap-2">
      <a href="<?= Helper::baseUrl('inspeksi') ?>" class="btn btn-sm btn-light border text-secondary">
        <i class="bi bi-arrow-left"></i>
      </a>
      <div>
        <h5 class="mb-0 fw-bold text-dark">Form Inspeksi Baru</h5>
        <span class="text-muted" style="font-size:12px">Checklist & dokumentasi kebersihan ruangan</span>
      </div>
    </div>
    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1" style="font-size:12px">
      <i class="bi bi-person-check me-1"></i><?= Helper::e(Session::get('user_name')) ?>
    </span>
  </div>

  <form method="POST" action="<?= Helper::baseUrl('inspeksi/create') ?>" id="formInspeksi" enctype="multipart/form-data">
    <?= Helper::csrfField() ?>

    <!-- Hidden Input untuk Nilai Form -->
    <input type="hidden" name="ruangan_id" id="valRuanganId" value="" required>
    <input type="hidden" name="assigned_user_id" id="valAssignedUserId" value="" required>

    <!-- Bagian 1: Pilih Lokasi & Petugas (Modal Custom Anti-Layar Hitam OS) -->
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-3 bg-white">
      <div class="fw-bold text-dark mb-2 small text-uppercase" style="letter-spacing:.05em">
        <i class="bi bi-geo-alt-fill text-success me-1"></i>1. Lokasi & Penanggung Jawab
      </div>

      <div class="row g-2">
        <!-- 1. Tombol Pilih Gedung -->
        <div class="col-12 col-md-4">
          <label class="form-label small fw-semibold text-muted mb-1">Gedung / Unit <span class="text-danger">*</span></label>
          <div class="picker-trigger-box border rounded-3 p-2 px-3 bg-white d-flex align-items-center justify-content-between shadow-xs" 
               onclick="openModalGedung()">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
              <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px; height:36px">
                <i class="bi bi-buildings-fill fs-6"></i>
              </div>
              <div class="overflow-hidden">
                <div id="displayGedungTitle" class="fw-bold text-dark small text-truncate">Pilih Gedung / Unit</div>
                <div id="displayGedungSub" class="text-muted" style="font-size:11px">Ketuk untuk memilih lokasi</div>
              </div>
            </div>
            <i class="bi bi-chevron-right text-muted small ms-2"></i>
          </div>
        </div>

        <!-- 2. Tombol Pilih Ruangan -->
        <div class="col-12 col-md-4">
          <label class="form-label small fw-semibold text-muted mb-1">Ruangan <span class="text-danger">*</span></label>
          <div id="boxTriggerRuangan" class="picker-trigger-box border rounded-3 p-2 px-3 bg-light d-flex align-items-center justify-content-between opacity-75" 
               style="cursor:not-allowed" onclick="openModalRuangan()">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
              <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px; height:36px" id="iconBoxRuangan">
                <i class="bi bi-door-open-fill fs-6"></i>
              </div>
              <div class="overflow-hidden">
                <div id="displayRuanganTitle" class="fw-bold text-muted small text-truncate">Pilih Ruangan</div>
                <div id="displayRuanganSub" class="text-muted" style="font-size:11px">Pilih gedung dahulu</div>
              </div>
            </div>
            <i class="bi bi-chevron-right text-muted small ms-2"></i>
          </div>
        </div>

        <!-- 3. Tombol Pilih Penanggung Jawab -->
        <div class="col-12 col-md-4">
          <label class="form-label small fw-semibold text-muted mb-1">Penanggung Jawab Area <span class="text-danger">*</span></label>
          <div class="picker-trigger-box border rounded-3 p-2 px-3 bg-white d-flex align-items-center justify-content-between shadow-xs" 
               onclick="openModalPetugas()">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
              <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px; height:36px">
                <i class="bi bi-person-badge-fill fs-6"></i>
              </div>
              <div class="overflow-hidden">
                <div id="displayPetugasTitle" class="fw-bold text-dark small text-truncate">Pilih Penanggung Jawab</div>
                <div id="displayPetugasSub" class="text-muted" style="font-size:11px">Otomatis terisi dari ruangan</div>
              </div>
            </div>
            <i class="bi bi-chevron-right text-muted small ms-2"></i>
          </div>
        </div>
      </div>

      <!-- Detail Tanggal & Waktu (Collapse Ringkas) -->
      <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
        <div class="small text-muted d-flex align-items-center gap-2">
          <span><i class="bi bi-calendar-event me-1"></i><?= date('d M Y') ?></span>
          <span>•</span>
          <span><i class="bi bi-clock me-1"></i><?= date('H:i') ?> WIB</span>
        </div>
        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-success" onclick="toggleWaktuInput()">
          <i class="bi bi-pencil-square me-1"></i>Ubah Waktu
        </button>
      </div>

      <div id="waktuWrap" class="row g-2 mt-1 d-none">
        <div class="col-6">
          <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-6">
          <input type="time" name="waktu_mulai" class="form-control form-control-sm" value="<?= date('H:i') ?>">
        </div>
      </div>
    </div>

    <!-- Bagian 2: Foto Utama Kondisi Ruangan (Buka Kamera Langsung) -->
    <div id="fotoRuanganSection" class="card border-0 shadow-sm rounded-3 p-3 mb-3 bg-white d-none">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="fw-bold text-dark small text-uppercase" style="letter-spacing:.05em">
          <i class="bi bi-camera-fill text-success me-1"></i>2. Foto Kondisi Ruangan
        </div>
        <span class="badge bg-light text-muted border">Opsional</span>
      </div>

      <div class="p-3 rounded-3 border-2 border-dashed text-center bg-light" id="boxFotoRuangan">
        <div id="fotoRuanganPrompt">
          <i class="bi bi-camera text-success display-6 d-block mb-2"></i>
          <label for="inputFotoRuangan" class="btn btn-success btn-sm px-3 py-2 shadow-sm" style="cursor:pointer">
            <i class="bi bi-camera-fill me-1"></i>Buka Kamera HP — Foto Ruangan
          </label>
          <div class="text-muted mt-2" style="font-size:11px">Ambil foto suasana umum ruangan sebelum/saat mulai inspeksi</div>
        </div>

        <input type="file" name="foto_ruangan" id="inputFotoRuangan"
               accept="image/*" capture="environment" class="d-none"
               onchange="previewFotoRuangan(this)">

        <div id="fotoRuanganPreview" class="d-none flex-column align-items-center">
          <img id="imgFotoRuangan" src="" class="img-fluid rounded-3 border shadow-sm mb-2" style="max-height:180px; object-fit:cover">
          <div class="d-flex gap-2">
            <label for="inputFotoRuangan" class="btn btn-outline-success btn-sm py-1 px-3" style="cursor:pointer; font-size:12px">
              <i class="bi bi-arrow-repeat me-1"></i>Ganti Foto
            </label>
            <button type="button" class="btn btn-outline-danger btn-sm py-1 px-3" style="font-size:12px" onclick="hapusFotoRuangan()">
              <i class="bi bi-trash me-1"></i>Hapus
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bagian 3: Checklist Items -->
    <div id="checklistArea" class="d-none">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="fw-bold text-dark small text-uppercase" style="letter-spacing:.05em">
          <i class="bi bi-list-check text-success me-1"></i>3. Checklist Kebersihan
          <span id="checklistCountBadge" class="badge bg-success ms-1"></span>
        </div>
      </div>

      <!-- Skor Preview Mini -->
      <div class="row g-2 mb-3 text-center">
        <div class="col-4">
          <div class="p-2 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25">
            <div class="fw-bold fs-6 text-success" id="previewBersih">0</div>
            <div style="font-size:11px" class="text-muted">Bersih</div>
          </div>
        </div>
        <div class="col-4">
          <div class="p-2 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-25">
            <div class="fw-bold fs-6 text-warning" id="previewKurang">0</div>
            <div style="font-size:11px" class="text-muted">Kurang</div>
          </div>
        </div>
        <div class="col-4">
          <div class="p-2 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25">
            <div class="fw-bold fs-6 text-danger" id="previewKotor">0</div>
            <div style="font-size:11px" class="text-muted">Kotor</div>
          </div>
        </div>
      </div>

      <!-- Container Item Checklist Dinamis -->
      <div id="checklistItems"></div>

      <!-- Catatan Umum -->
      <div class="card border-0 shadow-sm rounded-3 p-3 mt-3 bg-white">
        <label class="form-label fw-semibold small text-muted mb-1">
          <i class="bi bi-chat-left-text me-1"></i>Catatan Umum / Rekomendasi Tambahan
        </label>
        <textarea name="catatan_umum" class="form-control" rows="2" placeholder="Catatan temuan khusus atau pesan untuk penanggung jawab area..."></textarea>
      </div>

      <!-- Tombol Aksi Simpan (Bottom) -->
      <div class="mt-4 mb-5 d-flex gap-2">
        <button type="submit" name="is_draft" value="0" class="btn btn-success flex-grow-1 py-2 fw-semibold shadow-sm">
          <i class="bi bi-check-circle-fill me-1"></i>Simpan Inspeksi (Final)
        </button>
        <button type="submit" name="is_draft" value="1" class="btn btn-outline-secondary px-3 py-2">
          <i class="bi bi-save me-1"></i>Draft
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div id="loadingChecklist" class="text-center py-5 d-none">
      <div class="spinner-border text-success mb-2" role="status"></div>
      <div class="text-muted small fw-semibold">Memuat checklist ruangan otomatis...</div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="alert alert-warning d-none rounded-3 shadow-sm my-3">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      Ruangan ini belum memiliki standar checklist. Silakan tambahkan di menu <a href="<?= Helper::baseUrl('ruangan') ?>" class="alert-link">Kelola Ruangan</a>.
    </div>

    <!-- Floating Sticky Bottom Bar untuk Mobile (Mudah Disimpan) -->
    <div id="stickyBottomBar" class="d-none position-fixed bottom-0 start-0 end-0 bg-white border-top shadow-lg p-2 px-3" style="z-index:1020">
      <div class="d-flex align-items-center justify-content-between gap-2 max-w-screen" style="max-width:600px; margin:0 auto">
        <div>
          <div class="small fw-bold text-dark" id="mobileProgressText">0 / 0 Terisi</div>
          <div style="font-size:10px" class="text-muted">Skor: <span id="mobileSkorText" class="fw-bold text-success">0</span></div>
        </div>
        <button type="submit" name="is_draft" value="0" class="btn btn-success btn-sm px-4 py-2 fw-semibold shadow-sm">
          <i class="bi bi-check-lg me-1"></i>Simpan Final
        </button>
      </div>
    </div>

  </form>
</div>

<!-- ============================================================== -->
<!-- MODAL 1: PILIH GEDUNG (Clean White Mobile Sheet - No Black OS) -->
<!-- ============================================================== -->
<div class="modal fade" id="modalPickerGedung" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-4 bg-white">
      <div class="modal-header border-bottom py-3">
        <h6 class="modal-title fw-bold text-dark mb-0">
          <i class="bi bi-buildings-fill text-success me-2"></i>Pilih Gedung / Area
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3">
        <?php
        $gedungGrouped = [];
        foreach ($gedungList as $g) {
          $gedungGrouped[$g['unit']][] = $g;
        }
        foreach ($gedungGrouped as $unit => $listGedung):
        ?>
          <div class="mb-3">
            <div class="small fw-bold text-success text-uppercase mb-2 ps-2 border-start border-3 border-success">
              <?= Helper::e(Helper::unitLabel($unit)) ?>
            </div>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($listGedung as $g): ?>
                <div class="p-3 rounded-3 border bg-white d-flex align-items-center justify-content-between picker-card-item cursor-pointer"
                     onclick="pilihGedung(<?= $g['id'] ?>, '<?= Helper::e(addslashes($g['nama'])) ?>', '<?= Helper::e($g['unit']) ?>')">
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px">
                      <i class="bi bi-building fs-6"></i>
                    </div>
                    <div>
                      <div class="fw-bold text-dark small"><?= Helper::e($g['nama']) ?></div>
                      <div class="text-muted" style="font-size:11px"><?= $g['total_ruangan'] ?> Ruangan terdaftar</div>
                    </div>
                  </div>
                  <i class="bi bi-chevron-right text-muted small"></i>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: PILIH RUANGAN (Clean White Mobile Sheet - No Black OS) -->
<!-- ============================================================== -->
<div class="modal fade" id="modalPickerRuangan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-4 bg-white">
      <div class="modal-header border-bottom py-3">
        <div>
          <h6 class="modal-title fw-bold text-dark mb-0">
            <i class="bi bi-door-open-fill text-success me-2"></i>Pilih Ruangan
          </h6>
          <div id="modalRuanganGedungSubtitle" class="text-muted" style="font-size:11px">Gedung: -</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3">
        <!-- Search Input -->
        <div class="input-group mb-3">
          <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
          <input type="text" id="searchRuanganInput" class="form-control bg-light border-start-0" 
                 placeholder="Cari nama ruangan..." onkeyup="filterRuanganList(this.value)">
        </div>

        <div id="listRuanganContainer" class="d-flex flex-column gap-2">
          <!-- Diisi dinamis via JS -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 3: PILIH PENANGGUNG JAWAB (Clean White Mobile Sheet)     -->
<!-- ============================================================== -->
<div class="modal fade" id="modalPickerPetugas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-4 bg-white">
      <div class="modal-header border-bottom py-3">
        <h6 class="modal-title fw-bold text-dark mb-0">
          <i class="bi bi-person-badge-fill text-success me-2"></i>Pilih Penanggung Jawab Area
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3">
        <div class="d-flex flex-column gap-2">
          <?php foreach ($petugas as $p): ?>
            <div class="p-3 rounded-3 border bg-white d-flex align-items-center justify-content-between picker-card-item cursor-pointer"
                 onclick="pilihPetugas(<?= $p['id'] ?>, '<?= Helper::e(addslashes($p['name'])) ?>', '<?= Helper::e($p['role']) ?>')">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width:38px; height:38px; font-size:14px">
                  <?= strtoupper(substr($p['name'], 0, 1)) ?>
                </div>
                <div>
                  <div class="fw-bold text-dark small"><?= Helper::e($p['name']) ?></div>
                  <span class="badge bg-light text-muted border" style="font-size:10px"><?= ucfirst($p['role']) ?></span>
                </div>
              </div>
              <i class="bi bi-check-circle text-muted fs-5"></i>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Foto Popup Fullscreen -->
<div class="modal fade" id="modalFotoPopup" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark border-0">
      <div class="modal-body p-1 text-center">
        <img id="imgPopupFull" src="" class="img-fluid rounded" style="max-height:80vh">
      </div>
    </div>
  </div>
</div>

<script>
// Data Master
const allRuangan = <?= json_encode($ruangan) ?>;
const allPetugas = <?= json_encode(array_column($petugas, 'name', 'id')) ?>;

let currentSelectedGedungId = null;
let currentGedungRooms = [];

// 1. Modal Triggers
function openModalGedung() {
  new bootstrap.Modal(document.getElementById('modalPickerGedung')).show();
}

function openModalRuangan() {
  if (!currentSelectedGedungId) {
    Swal.fire({icon:'info', title:'Pilih Gedung Dahulu', text:'Silakan tentukan gedung/unit terlebih dahulu.', timer:1800, showConfirmButton:false});
    openModalGedung();
    return;
  }
  new bootstrap.Modal(document.getElementById('modalPickerRuangan')).show();
}

function openModalPetugas() {
  new bootstrap.Modal(document.getElementById('modalPickerPetugas')).show();
}

// 2. Pilih Gedung
function pilihGedung(id, nama, unit) {
  currentSelectedGedungId = id;
  document.getElementById('displayGedungTitle').textContent = nama;
  document.getElementById('displayGedungSub').textContent = 'Unit: ' + unit.toUpperCase();

  // Reset Ruangan
  document.getElementById('valRuanganId').value = '';
  document.getElementById('displayRuanganTitle').textContent = 'Pilih Ruangan';
  document.getElementById('displayRuanganSub').textContent = 'Ketuk untuk memilih ruangan';
  
  // Enable Trigger Ruangan
  const boxRuangan = document.getElementById('boxTriggerRuangan');
  boxRuangan.classList.remove('bg-light', 'opacity-75');
  boxRuangan.classList.add('bg-white');
  boxRuangan.style.cursor = 'pointer';
  document.getElementById('iconBoxRuangan').className = 'rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center flex-shrink-0';

  // Siapkan List Ruangan untuk Modal 2
  currentGedungRooms = allRuangan.filter(r => r.gedung_id == id);
  renderRuanganModalList(currentGedungRooms);
  document.getElementById('modalRuanganGedungSubtitle').textContent = 'Gedung: ' + nama;

  // Tutup Modal Gedung
  bootstrap.Modal.getInstance(document.getElementById('modalPickerGedung'))?.hide();

  // Otomatis Buka Modal Ruangan agar cepat
  setTimeout(() => {
    openModalRuangan();
  }, 350);
}

// Render List Ruangan di Modal
function renderRuanganModalList(rooms) {
  const container = document.getElementById('listRuanganContainer');
  container.innerHTML = '';

  if (rooms.length === 0) {
    container.innerHTML = '<div class="alert alert-light text-center small text-muted">Belum ada ruangan di gedung ini.</div>';
    return;
  }

  rooms.forEach(r => {
    const el = document.createElement('div');
    el.className = 'p-3 rounded-3 border bg-white d-flex align-items-center justify-content-between picker-card-item cursor-pointer';
    el.onclick = () => pilihRuangan(r.id, r.nama, r.lantai_nama, r.assigned_user_id);
    el.innerHTML = `
      <div>
        <div class="fw-bold text-dark small">${escHtml(r.nama)}</div>
        <div class="text-muted" style="font-size:11px">
          <i class="bi bi-layers me-1"></i>${escHtml(r.lantai_nama || 'Lantai 1')}
          ${r.kode ? ' • Kode: ' + escHtml(r.kode) : ''}
        </div>
      </div>
      <i class="bi bi-chevron-right text-muted small"></i>
    `;
    container.appendChild(el);
  });
}

function filterRuanganList(keyword) {
  const q = keyword.toLowerCase();
  const filtered = currentGedungRooms.filter(r => r.nama.toLowerCase().includes(q) || (r.lantai_nama && r.lantai_nama.toLowerCase().includes(q)));
  renderRuanganModalList(filtered);
}

// 3. Pilih Ruangan
function pilihRuangan(id, nama, lantai, defaultAssignedId) {
  document.getElementById('valRuanganId').value = id;
  document.getElementById('displayRuanganTitle').textContent = nama;
  document.getElementById('displayRuanganSub').textContent = lantai ? 'Lantai: ' + lantai : 'Ruangan Aktif';

  // Auto set Penanggung Jawab jika ada
  if (defaultAssignedId && allPetugas[defaultAssignedId]) {
    pilihPetugas(defaultAssignedId, allPetugas[defaultAssignedId], 'Petugas PJ Area');
  }

  // Tutup Modal Ruangan
  bootstrap.Modal.getInstance(document.getElementById('modalPickerRuangan'))?.hide();

  // Buka Foto Ruangan & Muat Checklist
  document.getElementById('fotoRuanganSection').classList.remove('d-none');
  loadChecklistOtomatis(id);
}

// 4. Pilih Petugas
function pilihPetugas(id, name, role) {
  document.getElementById('valAssignedUserId').value = id;
  document.getElementById('displayPetugasTitle').textContent = name;
  document.getElementById('displayPetugasSub').textContent = 'Role: ' + (role ? role.toUpperCase() : 'PETUGAS');

  bootstrap.Modal.getInstance(document.getElementById('modalPickerPetugas'))?.hide();
}

// 5. Muat Checklist Otomatis
function loadChecklistOtomatis(ruanganId) {
  document.getElementById('checklistArea').classList.add('d-none');
  document.getElementById('emptyState').classList.add('d-none');
  document.getElementById('loadingChecklist').classList.remove('d-none');
  document.getElementById('stickyBottomBar').classList.add('d-none');

  fetch('<?= Helper::baseUrl('inspeksi/checklist') ?>?ruangan_id=' + ruanganId, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('loadingChecklist').classList.add('d-none');

    if (!data.checklist || data.checklist.length === 0) {
      document.getElementById('emptyState').classList.remove('d-none');
      return;
    }

    renderChecklist(data.checklist);
    document.getElementById('checklistArea').classList.remove('d-none');
    document.getElementById('stickyBottomBar').classList.remove('d-none');
  })
  .catch(() => {
    document.getElementById('loadingChecklist').classList.add('d-none');
    Swal.fire({icon:'error', title:'Gagal memuat checklist', text:'Periksa koneksi jaringan.'});
  });
}

function renderChecklist(items) {
  const container = document.getElementById('checklistItems');
  document.getElementById('checklistCountBadge').textContent = items.length + ' Item';

  let html = '';
  let currentTipe = '';

  items.forEach((item, idx) => {
    if (item.tipe_ruangan !== currentTipe) {
      currentTipe = item.tipe_ruangan;
      if (idx > 0) html += '</div>';
      html += `<div class="mb-3">
        <div class="small fw-bold text-success text-uppercase mb-2 ps-2 border-start border-3 border-success">
          ${escHtml(item.tipe_ruangan.replace(/_/g,' '))}
        </div>`;
    }

    html += `
    <div class="card border shadow-sm rounded-3 mb-2 p-3 checklist-card" id="card-${item.id}">
      <div class="d-flex align-items-start gap-2 mb-2">
        <span class="badge bg-light text-muted border mt-1" style="font-size:11px">${idx+1}</span>
        <div class="flex-grow-1">
          <div class="fw-bold text-dark" style="font-size:13.5px">${escHtml(item.aspek)}</div>
          ${item.deskripsi ? `<div class="text-muted small" style="font-size:11.5px">${escHtml(item.deskripsi)}</div>` : ''}
        </div>
      </div>

      <!-- 3 Pilihan Kondisi Touch-Friendly -->
      <div class="btn-group w-100 mb-2" role="group">
        <input type="radio" class="btn-check" name="kondisi[${item.id}]" id="opt_b_${item.id}" value="bersih"
               onchange="onKondisiSelect(${item.id}, 'bersih')" required>
        <label class="btn btn-outline-success py-2 fw-semibold" for="opt_b_${item.id}" style="font-size:12.5px">
          <i class="bi bi-check-lg me-1"></i>Bersih
        </label>

        <input type="radio" class="btn-check" name="kondisi[${item.id}]" id="opt_k_${item.id}" value="kurang_bersih"
               onchange="onKondisiSelect(${item.id}, 'kurang_bersih')">
        <label class="btn btn-outline-warning py-2 fw-semibold" for="opt_k_${item.id}" style="font-size:12.5px">
          <i class="bi bi-exclamation-triangle me-1"></i>Kurang
        </label>

        <input type="radio" class="btn-check" name="kondisi[${item.id}]" id="opt_t_${item.id}" value="kotor"
               onchange="onKondisiSelect(${item.id}, 'kotor')">
        <label class="btn btn-outline-danger py-2 fw-semibold" for="opt_t_${item.id}" style="font-size:12.5px">
          <i class="bi bi-x-lg me-1"></i>Kotor
        </label>
      </div>

      <!-- Area Catatan Temuan -->
      <div id="catatan_box_${item.id}" class="d-none mb-2">
        <textarea name="catatan[${item.id}]" class="form-control form-control-sm bg-light"
                  rows="2" placeholder="Tuliskan catatan temuan / bagian yang kotor..."></textarea>
      </div>

      <!-- Tombol Buka Kamera Item Checklist -->
      <div class="p-2 rounded-2 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2">
        <label for="foto_item_${item.id}" class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-1 mb-0 py-1 px-2"
               style="font-size:11.5px; cursor:pointer" id="btn_cam_${item.id}">
          <i class="bi bi-camera-fill text-success"></i>
          <span id="label_cam_${item.id}">Buka Kamera — Foto Temuan</span>
        </label>
        <input type="file" name="foto[${item.id}]" id="foto_item_${item.id}"
               accept="image/*" capture="environment" class="d-none"
               onchange="onItemFotoCaptured(this, ${item.id})">

        <!-- Preview Foto Kecil -->
        <div id="preview_item_${item.id}" class="d-none align-items-center gap-2">
          <img id="img_item_${item.id}" src="" class="rounded border shadow-xs" style="width:40px; height:40px; object-fit:cover; cursor:pointer" onclick="showPopupPhoto(this.src)">
          <button type="button" class="btn btn-sm btn-link text-danger p-0" title="Hapus foto" onclick="hapusFotoItem(${item.id})">
            <i class="bi bi-x-circle-fill fs-5"></i>
          </button>
        </div>
      </div>
    </div>`;
  });

  if (items.length) html += '</div>';
  container.innerHTML = html;
  hitungProgress();
}

function onKondisiSelect(itemId, kondisi) {
  const card = document.getElementById(`card-${itemId}`);
  const catBox = document.getElementById(`catatan_box_${itemId}`);
  const btnCam = document.getElementById(`btn_cam_${itemId}`);

  card.classList.remove('border-success', 'border-warning', 'border-danger');

  if (kondisi === 'bersih') {
    card.classList.add('border-success');
    catBox.classList.add('d-none');
    if (btnCam) btnCam.className = "btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 mb-0 py-1 px-2";
  } else if (kondisi === 'kurang_bersih') {
    card.classList.add('border-warning');
    catBox.classList.remove('d-none');
    if (btnCam) btnCam.className = "btn btn-sm btn-outline-warning text-dark d-inline-flex align-items-center gap-1 mb-0 py-1 px-2 fw-semibold";
  } else {
    card.classList.add('border-danger');
    catBox.classList.remove('d-none');
    if (btnCam) btnCam.className = "btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 mb-0 py-1 px-2 fw-semibold";
  }

  hitungProgress();
}

function compressImageFile(file, maxDim, quality, cb) {
  if (!file || !file.type.startsWith('image/')) { cb(file, null); return; }
  const reader = new FileReader();
  reader.onload = function(e) {
    const img = new Image();
    img.onload = function() {
      let w = img.width, h = img.height;
      if (w > maxDim || h > maxDim) {
        if (w > h) { h = Math.round((h * maxDim) / w); w = maxDim; }
        else { w = Math.round((w * maxDim) / h); h = maxDim; }
      }
      const canvas = document.createElement('canvas');
      canvas.width = w; canvas.height = h;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0, w, h);
      canvas.toBlob(function(blob) {
        if (!blob) { cb(file, e.target.result); return; }
        try {
          const compFile = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg', lastModified: Date.now() });
          cb(compFile, canvas.toDataURL('image/jpeg', quality));
        } catch(err) {
          cb(file, e.target.result);
        }
      }, 'image/jpeg', quality);
    };
    img.src = e.target.result;
  };
  reader.readAsDataURL(file);
}

function onItemFotoCaptured(input, itemId) {
  const file = input.files[0];
  if (!file) return;

  compressImageFile(file, 1280, 0.8, function(compFile, dataUrl) {
    if (window.DataTransfer && compFile !== file) {
      try {
        const dt = new DataTransfer();
        dt.items.add(compFile);
        input.files = dt.files;
      } catch(e) {}
    }
    document.getElementById(`img_item_${itemId}`).src = dataUrl || URL.createObjectURL(compFile);
    document.getElementById(`preview_item_${itemId}`).classList.remove('d-none');
    document.getElementById(`preview_item_${itemId}`).classList.add('d-flex');
    document.getElementById(`label_cam_${itemId}`).textContent = 'Ganti Foto';
  });
}

function hapusFotoItem(itemId) {
  const input = document.getElementById(`foto_item_${itemId}`);
  if (input) input.value = '';
  const preview = document.getElementById(`preview_item_${itemId}`);
  if (preview) {
    preview.classList.add('d-none');
    preview.classList.remove('d-flex');
  }
  document.getElementById(`label_cam_${itemId}`).textContent = 'Buka Kamera — Foto Temuan';
}

function previewFotoRuangan(input) {
  const file = input.files[0];
  if (!file) return;

  compressImageFile(file, 1280, 0.82, function(compFile, dataUrl) {
    if (window.DataTransfer && compFile !== file) {
      try {
        const dt = new DataTransfer();
        dt.items.add(compFile);
        input.files = dt.files;
      } catch(e) {}
    }
    document.getElementById('imgFotoRuangan').src = dataUrl || URL.createObjectURL(compFile);
    document.getElementById('fotoRuanganPrompt').classList.add('d-none');
    document.getElementById('fotoRuanganPreview').classList.remove('d-none');
    document.getElementById('fotoRuanganPreview').classList.add('d-flex');
  });
}

function hapusFotoRuangan() {
  const input = document.getElementById('inputFotoRuangan');
  if (input) input.value = '';
  document.getElementById('fotoRuanganPrompt').classList.remove('d-none');
  document.getElementById('fotoRuanganPreview').classList.add('d-none');
  document.getElementById('fotoRuanganPreview').classList.remove('d-flex');
}

function showPopupPhoto(src) {
  document.getElementById('imgPopupFull').src = src;
  new bootstrap.Modal(document.getElementById('modalFotoPopup')).show();
}

function toggleWaktuInput() {
  document.getElementById('waktuWrap').classList.toggle('d-none');
}

function hitungProgress() {
  const checked = document.querySelectorAll('.btn-check:checked');
  const allRadios = document.querySelectorAll('.btn-check');
  const total = allRadios.length / 3;
  const filled = checked.length;

  let b = 0, k = 0, t = 0;
  checked.forEach(r => {
    if (r.value === 'bersih') b++;
    else if (r.value === 'kurang_bersih') k++;
    else if (r.value === 'kotor') t++;
  });

  document.getElementById('previewBersih').textContent = b;
  document.getElementById('previewKurang').textContent = k;
  document.getElementById('previewKotor').textContent = t;

  const skor = filled > 0 ? Math.round(((b * 100) + (k * 70) + (t * 30)) / filled) : 0;

  document.getElementById('mobileProgressText').textContent = `${filled} / ${total} Terisi`;
  document.getElementById('mobileSkorText').textContent = skor;
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<style>
/* Custom Styling Anti-Layar Hitam & Touch-Friendly */
.picker-trigger-box {
  cursor: pointer;
  min-height: 54px;
  transition: all .2s ease;
}
.picker-trigger-box:hover {
  border-color: #1a56db !important;
  background-color: #f0f7ff !important;
}
.picker-card-item {
  transition: all .15s ease;
  cursor: pointer;
}
.picker-card-item:hover, .picker-card-item:active {
  background-color: #e0f2fe !important;
  border-color: #1a56db !important;
  transform: translateY(-1px);
}
.cursor-pointer {
  cursor: pointer;
}
.btn-check:checked + .btn-outline-success {
  background-color: #2d8653 !important;
  color: #fff !important;
  border-color: #2d8653 !important;
}
.btn-check:checked + .btn-outline-warning {
  background-color: #d97706 !important;
  color: #fff !important;
  border-color: #d97706 !important;
}
.btn-check:checked + .btn-outline-danger {
  background-color: #dc2626 !important;
  color: #fff !important;
  border-color: #dc2626 !important;
}
.checklist-card {
  transition: border-color .2s, box-shadow .2s;
}
</style>
