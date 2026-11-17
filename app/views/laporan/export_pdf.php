<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Inspeksi — Al Wildan</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Segoe UI',Arial,sans-serif; font-size:12px; color:#1a2035; background:#fff; }

.no-print { padding:10px 24px; background:#f8f9fa; border-bottom:1px solid #dee2e6; display:flex; gap:8px; }
.no-print button { padding:6px 16px; border-radius:5px; cursor:pointer; font-size:12px; border:none; }
.btn-print { background:#1a5c38; color:#fff; }
.btn-close2 { background:#fff; border:1px solid #ccc !important; }

.wrap { padding:20px 28px; max-width:1000px; margin:0 auto; }

/* Header */
.rpt-header { display:flex; justify-content:space-between; align-items:flex-start;
              padding-bottom:14px; border-bottom:3px solid #1a5c38; margin-bottom:16px; }
.rpt-title  { font-size:18px; font-weight:700; color:#1a5c38; }
.rpt-sub    { font-size:12px; color:#666; margin-top:2px; }
.rpt-meta   { text-align:right; font-size:11px; color:#888; }

/* Filter info */
.filter-info { background:#f0faf4; border:1px solid #d1fae5; border-radius:6px; padding:10px 14px; margin-bottom:14px; font-size:11px; }

/* Ringkasan */
.stats-row { display:flex; gap:12px; margin-bottom:16px; }
.stat-box  { flex:1; text-align:center; padding:10px 8px; border-radius:6px; }
.stat-val  { font-size:18px; font-weight:700; }
.stat-lbl  { font-size:10px; color:#666; margin-top:1px; }

/* Tabel */
table { width:100%; border-collapse:collapse; font-size:11px; }
thead tr { background:#1a5c38; color:#fff; }
th { padding:7px 8px; text-align:left; font-weight:600; }
td { padding:6px 8px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
tr:nth-child(even) td { background:#fafbfc; }
.badge { display:inline-block; padding:1px 7px; border-radius:8px; font-size:10px; font-weight:600; }
.b-s { background:#dcfce7; color:#166534; }
.b-k { background:#fef9c3; color:#854d0e; }
.b-d { background:#fee2e2; color:#991b1b; }
.b-u { background:#e5e7eb; color:#374151; }

.footer { margin-top:16px; padding-top:10px; border-top:1px solid #e5e7eb;
          display:flex; justify-content:space-between; font-size:10px; color:#aaa; }

@media print {
  .no-print { display:none !important; }
  @page { margin:12mm 14mm 16mm; }
  body { font-size:11px; }
}
</style>
</head>
<body>

<div class="no-print">
  <button class="btn-print" onclick="window.print()">🖨 Cetak / Simpan PDF</button>
  <button class="btn-close2" onclick="window.close()">✕ Tutup</button>
</div>

<div class="wrap">

  <!-- Header -->
  <div class="rpt-header">
    <div>
      <div class="rpt-title">📋 Laporan Inspeksi Kebersihan</div>
      <div class="rpt-sub">Al-Wildan Islamic School 8 Kemang Bogor — Yayasan Ambadar</div>
      <div class="rpt-sub" style="margin-top:4px;color:#888">
        Periode: <?= Helper::formatDate($filters['tanggal_dari'],'d M Y') ?>
        s/d <?= Helper::formatDate($filters['tanggal_sampai'],'d M Y') ?>
      </div>
    </div>
    <div class="rpt-meta">
      <div>Dicetak: <?= Helper::formatDatetime(date('Y-m-d H:i:s')) ?></div>
      <?php if ($filters['unit']): ?>
        <div>Unit: <?= strtoupper($filters['unit']) ?></div>
      <?php endif; ?>
      <?php if ($filters['status']): ?>
        <div>Status: <?= Helper::statusLabel($filters['status']) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Ringkasan -->
  <div class="stats-row">
    <div class="stat-box" style="background:#f1f5f9">
      <div class="stat-val"><?= $ringkasan['total'] ?></div>
      <div class="stat-lbl">Total Inspeksi</div>
    </div>
    <div class="stat-box" style="background:#dcfce7">
      <div class="stat-val" style="color:#166534"><?= $ringkasan['bersih'] ?></div>
      <div class="stat-lbl">Bersih</div>
    </div>
    <div class="stat-box" style="background:#fef9c3">
      <div class="stat-val" style="color:#854d0e"><?= $ringkasan['kurang_bersih'] ?></div>
      <div class="stat-lbl">Kurang Bersih</div>
    </div>
    <div class="stat-box" style="background:#fee2e2">
      <div class="stat-val" style="color:#991b1b"><?= $ringkasan['kotor'] ?></div>
      <div class="stat-lbl">Kotor</div>
    </div>
    <div class="stat-box" style="background:#eff6ff">
      <?php $sc = $ringkasan['avg_skor']>=85?'#166534':($ringkasan['avg_skor']>=55?'#854d0e':'#991b1b'); ?>
      <div class="stat-val" style="color:<?= $sc ?>"><?= number_format($ringkasan['avg_skor'],1) ?></div>
      <div class="stat-lbl">Rata-rata Skor</div>
    </div>
  </div>

  <!-- Tabel data -->
  <table>
    <thead>
      <tr>
        <th style="width:28px">No</th>
        <th style="width:80px">Tanggal</th>
        <th>Ruangan</th>
        <th>Gedung</th>
        <th style="width:50px">Unit</th>
        <th>Petugas PJ</th>
        <th>Inspektor</th>
        <th style="width:44px;text-align:center">Skor</th>
        <th style="width:80px;text-align:center">Status</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($rows)): ?>
      <tr><td colspan="9" style="text-align:center;padding:16px;color:#888">Tidak ada data.</td></tr>
    <?php endif; ?>
    <?php $no=1; foreach ($rows as $r):
      $bc = match($r['status']??''){'bersih'=>'b-s','kurang_bersih'=>'b-k','kotor'=>'b-d',default=>'b-u'};
    ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= Helper::formatDate($r['tanggal'],'d/m/Y') ?></td>
        <td>
          <strong><?= Helper::e($r['ruangan_nama']) ?></strong>
          <span style="color:#888;font-size:10px"> (<?= Helper::e($r['ruangan_kode']) ?>)</span>
        </td>
        <td><?= Helper::e($r['gedung_nama']) ?></td>
        <td style="text-align:center">
          <?php $uc=match($r['gedung_unit']){'smp'=>'#16a34a','smk'=>'#2563eb','yayasan'=>'#d97706','masjid'=>'#0891b2',default=>'#888'}; ?>
          <span style="font-weight:700;color:<?= $uc ?>"><?= strtoupper($r['gedung_unit']) ?></span>
        </td>
        <td><?= Helper::e($r['assigned_nama'] ?? '—') ?></td>
        <td><?= Helper::e($r['inspected_nama'] ?? '—') ?></td>
        <td style="text-align:center;font-weight:700;color:<?= $r['skor_total']>=85?'#166534':($r['skor_total']>=55?'#854d0e':'#991b1b') ?>">
          <?= $r['skor_total'] !== null ? number_format((float)$r['skor_total'],1) : '—' ?>
        </td>
        <td style="text-align:center">
          <span class="badge <?= $bc ?>"><?= Helper::statusLabel($r['status']??'') ?></span>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="footer">
    <span>Total: <?= count($rows) ?> data</span>
    <span>Sistem Inspeksi Kebersihan — Yayasan Am Badar</span>
    <span>Dokumen Resmi — Konfidensial</span>
  </div>

</div>
</body>
</html>
