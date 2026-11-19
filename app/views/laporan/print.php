<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Inspeksi #<?= $inspeksi['id'] ?> — Al Wildan</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1a2035; background: #fff; }

/* Header */
.print-header { display:flex; align-items:center; justify-content:space-between; padding:20px 28px 16px; border-bottom:3px solid #1a5c38; margin-bottom:20px; }
.logo-area { display:flex; align-items:center; gap:14px; }
.logo-icon { width:52px; height:52px; background:#1a5c38; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px; }
.org-name { font-size:16px; font-weight:700; color:#1a5c38; }
.org-sub  { font-size:12px; color:#555; }
.doc-info { text-align:right; }
.doc-no   { font-size:15px; font-weight:700; color:#1a5c38; }
.doc-date { font-size:12px; color:#666; margin-top:2px; }

/* Skor badge besar */
.skor-badge { display:inline-flex; flex-direction:column; align-items:center; justify-content:center; width:80px; height:80px; border-radius:50%; border:3px solid; }
.skor-bersih        { border-color:#22c55e; color:#22c55e; }
.skor-kurang_bersih { border-color:#f59e0b; color:#f59e0b; }
.skor-kotor         { border-color:#ef4444; color:#ef4444; }
.skor-val  { font-size:22px; font-weight:700; line-height:1; }
.skor-lbl  { font-size:10px; margin-top:2px; }

/* Info grid */
.info-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; padding:0 0 16px; border-bottom:1px solid #e5e7eb; }
.info-block { background:#f8fafb; border-radius:8px; padding:12px 14px; }
.info-label { font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.04em; margin-bottom:4px; }
.info-value { font-size:13px; font-weight:600; }

/* Tabel checklist */
.section-title { font-size:13px; font-weight:700; color:#1a5c38; text-transform:uppercase; letter-spacing:.05em; margin:16px 0 8px; padding-bottom:4px; border-bottom:2px solid #e8f5ee; }
table { width:100%; border-collapse:collapse; margin-bottom:16px; }
th { background:#1a5c38; color:#fff; font-size:11px; font-weight:600; padding:7px 10px; text-align:left; }
td { padding:6px 10px; border-bottom:1px solid #f0f2f5; font-size:12px; vertical-align:top; }
tr:nth-child(even) td { background:#fafbfc; }
.badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; }
.badge-bersih        { background:#dcfce7; color:#166534; }
.badge-kurang_bersih { background:#fef9c3; color:#854d0e; }
.badge-kotor         { background:#fee2e2; color:#991b1b; }

/* Ringkasan bawah */
.summary-row { display:flex; gap:16px; margin:16px 0; flex-wrap:wrap; }
.summary-box { flex:1; min-width:100px; text-align:center; padding:10px; border-radius:8px; }
.summary-box.s-total  { background:#f1f5f9; }
.summary-box.s-b      { background:#dcfce7; }
.summary-box.s-k      { background:#fef9c3; }
.summary-box.s-d      { background:#fee2e2; }
.summary-val  { font-size:20px; font-weight:700; }
.summary-lbl  { font-size:11px; color:#666; }

/* Catatan */
.note-box { background:#fffbeb; border:1px solid #fcd34d; border-radius:8px; padding:12px 14px; margin:12px 0; }
.note-label { font-size:11px; font-weight:700; color:#92400e; margin-bottom:4px; }

/* Tanda tangan */
.ttd-area { display:grid; grid-template-columns:1fr 1fr 1fr; gap:24px; margin-top:28px; padding-top:16px; border-top:1px solid #e5e7eb; }
.ttd-box  { text-align:center; }
.ttd-line { margin:50px 20px 6px; border-bottom:1px solid #333; }
.ttd-name { font-size:12px; font-weight:600; }
.ttd-role { font-size:11px; color:#888; }

/* Footer */
.print-footer { margin-top:24px; padding-top:12px; border-top:1px solid #e5e7eb; display:flex; justify-content:space-between; font-size:11px; color:#999; }

/* Print styles */
@media print {
  .no-print { display:none !important; }
  body { font-size:12px; }
  .print-header { padding:14px 20px 12px; }
  @page { margin: 15mm 15mm 20mm; }
}
</style>
</head>
<body>

<!-- Tombol cetak (tidak muncul saat print) -->
<div class="no-print" style="padding:12px 28px;background:#f8f9fa;border-bottom:1px solid #dee2e6;display:flex;gap:8px;align-items:center">
  <button onclick="window.print()" style="padding:7px 18px;background:#1a5c38;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px">
    🖨 Cetak / Simpan PDF
  </button>
  <button onclick="window.close()" style="padding:7px 18px;background:#fff;border:1px solid #ccc;border-radius:6px;cursor:pointer;font-size:13px">
    ✕ Tutup
  </button>
</div>

<div style="padding:24px 32px;max-width:900px;margin:0 auto">

  <!-- Header dokumen -->
  <div class="print-header">
    <div class="logo-area">
      <div style="width:48px;height:48px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:3px;display:flex;align-items:center;justify-content:center;">
        <img src="<?= Helper::baseUrl('img/logo-alwildan.png') ?>" alt="Logo" style="width:100%;height:100%;object-fit:contain;">
      </div>
      <div>
        <div class="org-name" style="color:#0f2744;font-size:16px;font-weight:700;">AL-WILDAN ISLAMIC SCHOOL 8</div>
        <div class="org-sub" style="color:#475569;font-size:12px;">Kemang Bogor &bull; Laporan Resmi Inspeksi Kebersihan</div>
      </div>
    </div>
    <div class="doc-info">
      <div class="doc-no">Inspeksi #<?= $inspeksi['id'] ?></div>
      <div class="doc-date">Tanggal cetak: <?= Helper::formatDate(date('Y-m-d'),'d F Y') ?></div>
      <?php
      $skor   = (float)($inspeksi['skor_total'] ?? 0);
      $status = $inspeksi['status'] ?? 'kotor';
      $sc     = match($status){'bersih'=>'success','kurang_bersih'=>'warning',default=>'danger'};
      ?>
      <div style="margin-top:10px">
        <div class="skor-badge skor-<?= $status ?>">
          <span class="skor-val"><?= $skor > 0 ? number_format($skor,0) : '—' ?></span>
          <span class="skor-lbl"><?= Helper::statusLabel($status) ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Info inspeksi -->
  <div class="info-grid">
    <div class="info-block">
      <div class="info-label">Ruangan</div>
      <div class="info-value"><?= Helper::e($inspeksi['ruangan_nama']) ?></div>
      <div style="font-size:12px;color:#666;margin-top:2px"><?= Helper::e($inspeksi['ruangan_kode']) ?> — <?= Helper::e($inspeksi['lantai_nama']) ?></div>
    </div>
    <div class="info-block">
      <div class="info-label">Gedung / Unit</div>
      <div class="info-value"><?= Helper::e($inspeksi['gedung_nama']) ?></div>
      <div style="font-size:12px;color:#666;margin-top:2px"><?= strtoupper($inspeksi['gedung_unit'] ?? '') ?></div>
    </div>
    <div class="info-block">
      <div class="info-label">Petugas Penanggung Jawab Area</div>
      <div class="info-value"><?= Helper::e($inspeksi['assigned_nama'] ?? '—') ?></div>
    </div>
    <div class="info-block">
      <div class="info-label">Diinspeksi Oleh</div>
      <div class="info-value"><?= Helper::e($inspeksi['inspected_nama'] ?? '—') ?></div>
      <div style="font-size:12px;color:#666;margin-top:2px">
        <?= Helper::formatDate($inspeksi['tanggal'],'d F Y') ?>
        <?= $inspeksi['waktu_mulai'] ? ' pukul ' . $inspeksi['waktu_mulai'] : '' ?>
      </div>
    </div>
  </div>

  <!-- Dokumentasi Foto Ruangan -->
  <?php if (!empty($inspeksi['foto_ruangan'])): ?>
  <div style="margin-bottom:16px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; display:flex; align-items:center; gap:16px;">
    <img src="<?= Helper::uploadUrl("inspeksi/{$inspeksi['id']}/{$inspeksi['foto_ruangan']}") ?>" alt="Foto Ruangan" style="width:130px; height:90px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1;">
    <div>
      <div style="font-weight:700; font-size:12px; color:#1a5c38; margin-bottom:3px;">📷 Foto Dokumentasi Kondisi Ruangan</div>
      <div style="font-size:11px; color:#64748b;">Dokumentasi visual kondisi umum ruangan saat inspeksi dimulai.</div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Ringkasan skor -->
  <?php
  $b = $k = $d = 0;
  foreach ($details as $det) {
    if ($det['kondisi']==='bersih') $b++;
    elseif ($det['kondisi']==='kurang_bersih') $k++;
    else $d++;
  }
  $total = count($details);
  ?>
  <div class="summary-row">
    <div class="summary-box s-total">
      <div class="summary-val"><?= $total ?></div>
      <div class="summary-lbl">Total Item</div>
    </div>
    <div class="summary-box s-b">
      <div class="summary-val" style="color:#166534"><?= $b ?></div>
      <div class="summary-lbl">Bersih</div>
    </div>
    <div class="summary-box s-k">
      <div class="summary-val" style="color:#854d0e"><?= $k ?></div>
      <div class="summary-lbl">Kurang Bersih</div>
    </div>
    <div class="summary-box s-d">
      <div class="summary-val" style="color:#991b1b"><?= $d ?></div>
      <div class="summary-lbl">Kotor</div>
    </div>
    <div class="summary-box" style="background:#f1f5f9">
      <div class="summary-val" style="color:<?= $skor>=85?'#166534':($skor>=55?'#854d0e':'#991b1b') ?>">
        <?= number_format($skor,1) ?>
      </div>
      <div class="summary-lbl">Rata Skor</div>
    </div>
  </div>

  <!-- Catatan umum jika ada -->
  <?php if ($inspeksi['catatan_umum']): ?>
  <div class="note-box">
    <div class="note-label">📝 Catatan Umum</div>
    <div style="font-size:12px"><?= nl2br(Helper::e($inspeksi['catatan_umum'])) ?></div>
  </div>
  <?php endif; ?>

  <!-- Detail checklist -->
  <?php
  $grouped = [];
  foreach ($details as $d) {
    $grouped[$d['tipe_ruangan']][] = $d;
  }
  ?>
  <?php foreach ($grouped as $tipe => $items): ?>
  <div class="section-title">
    <?= Helper::e($tipeList[$tipe] ?? $tipe) ?>
  </div>
  <table>
    <thead>
      <tr>
        <th style="width:32px">#</th>
        <th>Aspek Kebersihan</th>
        <th style="width:110px">Kondisi</th>
        <th style="width:50px;text-align:center">Skor</th>
        <th style="width:90px;text-align:center">Foto Bukti</th>
        <th>Catatan Temuan</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $i => $det): ?>
      <tr>
        <td><?= $det['no_urut'] ?? ($i+1) ?></td>
        <td>
          <strong><?= Helper::e($det['aspek']) ?></strong>
          <?php if ($det['deskripsi']): ?>
            <div style="font-size:11px;color:#888;margin-top:1px"><?= Helper::e($det['deskripsi']) ?></div>
          <?php endif; ?>
        </td>
        <td>
          <span class="badge badge-<?= $det['kondisi'] ?>">
            <?= Helper::statusLabel($det['kondisi']) ?>
          </span>
        </td>
        <td style="text-align:center;font-weight:700;color:<?= $det['skor']>=85?'#166534':($det['skor']>=55?'#854d0e':'#991b1b') ?>">
          <?= $det['skor'] ?>
        </td>
        <td style="text-align:center">
          <?php if (!empty($det['foto'])): ?>
            <img src="<?= Helper::uploadUrl("inspeksi/{$inspeksi['id']}/{$det['foto']}") ?>" alt="Foto" style="width:65px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #ccc;">
          <?php else: ?>
            <span style="color:#aaa;font-size:11px">—</span>
          <?php endif; ?>
        </td>
        <td style="color:#666"><?= Helper::e($det['catatan'] ?? '—') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endforeach; ?>

  <!-- Area tanda tangan -->
  <div class="ttd-area">
    <div class="ttd-box">
      <div style="font-size:11px;color:#888">Diinspeksi oleh</div>
      <div class="ttd-line"></div>
      <div class="ttd-name"><?= Helper::e($inspeksi['inspected_nama'] ?? '________________') ?></div>
      <div class="ttd-role">Inspektor</div>
    </div>
    <div class="ttd-box">
      <div style="font-size:11px;color:#888">Petugas PJ Area</div>
      <div class="ttd-line"></div>
      <div class="ttd-name"><?= Helper::e($inspeksi['assigned_nama'] ?? '________________') ?></div>
      <div class="ttd-role">Penanggung Jawab</div>
    </div>
    <div class="ttd-box">
      <div style="font-size:11px;color:#888">Diketahui oleh</div>
      <div class="ttd-line"></div>
      <div class="ttd-name">________________</div>
      <div class="ttd-role">Supervisor / GA</div>
    </div>
  </div>

  <!-- Footer -->
  <div class="print-footer">
    <span>Dicetak: <?= Helper::formatDatetime(date('Y-m-d H:i:s')) ?></span>
    <span>Al-Wildan Islamic School 8 Kemang Bogor</span>
    <span>Dokumen Resmi &bull; Konfidensial</span>
  </div>

</div><!-- /container -->
<script>
// Auto print jika parameter print=1
if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
  window.addEventListener('load', () => setTimeout(() => window.print(), 500));
}
</script>
</body>
</html>
