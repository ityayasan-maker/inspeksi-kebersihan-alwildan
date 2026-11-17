<?php
$unitLabel = [
    'alwildan_8' => 'Al-Wildan 8',
    'tk'         => 'TK Al-Wildan',
    'sd'         => 'SD Al-Wildan',
    'smp'        => 'SMP Al-Wildan',
    'sma'        => 'SMA Al-Wildan',
    'asrama'     => 'Asrama',
    'fasilitas'  => 'Fasilitas'
];
$unitColor = [
    'alwildan_8' => '#1a56db',
    'tk'         => '#06b6d4',
    'sd'         => '#22c55e',
    'smp'        => '#3b82f6',
    'sma'        => '#8b5cf6',
    'asrama'     => '#f59e0b',
    'fasilitas'  => '#64748b'
];
?>

<!-- ── Kartu statistik ── -->
<div class="row g-3 mb-4 stagger-children">

  <?php
  $rataSkor = (float)($kartu['rata_skor'] ?? 0);
  $cards = [
    ['label'=>'Inspeksi Hari Ini', 'val'=>$kartu['inspeksi_hari_ini'] ?? 0, 'icon'=>'clipboard2-check',  'color'=>'success',   'sub'=>'Total: '.($kartu['total_inspeksi'] ?? 0)],
    ['label'=>'Inspeksi Bulan Ini','val'=>$kartu['inspeksi_bulan'] ?? 0,     'icon'=>'calendar-check',    'color'=>'primary',   'sub'=>date('F Y')],
    ['label'=>'Rata-rata Skor',    'val'=>number_format($rataSkor, 1),       'icon'=>'speedometer2',      'color'=>($rataSkor>=85?'success':($rataSkor>=55?'warning':'danger')), 'sub'=>'Semua waktu'],
    ['label'=>'Perlu Tindak Lanjut','val'=>$kartu['perlu_tindak'] ?? 0,      'icon'=>'exclamation-triangle','color'=>'danger',   'sub'=>'7 hari terakhir'],
  ];
  foreach ($cards as $c):
  ?>
  <div class="col-6 col-md-3 animate-fade-up">
    <div class="page-card h-100 p-3">
      <div class="d-flex align-items-start justify-content-between">
        <div>
          <div class="text-muted small mb-1"><?= $c['label'] ?></div>
          <div class="fw-bold fs-3 text-<?= $c['color'] ?> lh-1"><?= $c['val'] ?></div>
          <div class="text-muted mt-1" style="font-size:11px"><?= $c['sub'] ?></div>
        </div>
        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0
                    bg-<?= $c['color'] ?> bg-opacity-10"
             style="width:44px;height:44px;font-size:20px;color:var(--bs-<?= $c['color'] ?>)">
          <i class="bi bi-<?= $c['icon'] ?>"></i>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Baris tengah: Chart tren + Distribusi status ── -->
<div class="row g-3 mb-3">

  <!-- Chart tren 30 hari -->
  <div class="col-lg-8">
    <div class="page-card h-100">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up me-2 text-success"></i>Tren Inspeksi 30 Hari</h6>
        <div class="btn-group btn-group-sm">
          <button class="btn btn-outline-secondary active" onclick="switchChart('bar',this)">Bar</button>
          <button class="btn btn-outline-secondary" onclick="switchChart('line',this)">Line</button>
        </div>
      </div>
      <div style="position:relative;height:220px">
        <canvas id="chartTren"></canvas>
      </div>
    </div>
  </div>

  <!-- Distribusi status -->
  <div class="col-lg-4">
    <div class="page-card h-100">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2 text-success"></i>Distribusi Status</h6>
      </div>
      <div style="position:relative;height:160px">
        <canvas id="chartPie"></canvas>
      </div>
      <div class="d-flex justify-content-center gap-3 mt-2 flex-wrap">
        <span class="small d-flex align-items-center gap-1">
          <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#22c55e"></span>
          Bersih <strong id="statBersih"><?= $kartu['total_bersih'] ?></strong>
        </span>
        <span class="small d-flex align-items-center gap-1">
          <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#f59e0b"></span>
          Kurang <strong id="statKurang"><?= $kartu['total_kurang'] ?></strong>
        </span>
        <span class="small d-flex align-items-center gap-1">
          <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:#ef4444"></span>
          Kotor <strong id="statKotor"><?= $kartu['total_kotor'] ?></strong>
        </span>
      </div>
    </div>
  </div>
</div>

<!-- ── Baris bawah: Ranking + Area masalah + Aktivitas ── -->
<div class="row g-3 mb-3">

  <!-- Ranking petugas -->
  <div class="col-lg-4">
    <div class="page-card h-100">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-trophy me-2 text-warning"></i>Ranking Petugas</h6>
        <span class="small text-muted">30 hari</span>
      </div>
      <?php if (empty($ranking)): ?>
        <p class="text-muted small">Belum ada data.</p>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($ranking as $i => $p):
          $medal = match($i){0=>'🥇',1=>'🥈',2=>'🥉',default=>($i+1).'.'};
          $sc    = $p['avg_skor']>=85?'success':($p['avg_skor']>=55?'warning':'danger');
        ?>
        <div class="list-group-item px-0 py-2 d-flex align-items-center gap-2">
          <span style="min-width:28px;text-align:center;font-size:15px"><?= $medal ?></span>
          <div class="flex-shrink-0 rounded-circle bg-light d-flex align-items-center justify-content-center overflow-hidden"
               style="width:32px;height:32px;font-size:14px">
            <?php if ($p['photo']): ?>
              <img src="<?= Helper::uploadUrl('profile/'.$p['photo']) ?>" class="w-100 h-100" style="object-fit:cover">
            <?php else: ?><i class="bi bi-person text-secondary"></i><?php endif; ?>
          </div>
          <div class="flex-grow-1 min-width-0">
            <div class="fw-semibold small text-truncate"><?= Helper::e($p['name']) ?></div>
            <div class="text-muted" style="font-size:11px"><?= $p['total_inspeksi'] ?> inspeksi</div>
          </div>
          <div class="text-end flex-shrink-0">
            <span class="badge bg-<?= $sc ?>"><?= number_format($p['avg_skor'],1) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Area bermasalah -->
  <div class="col-lg-4">
    <div class="page-card h-100">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Area Bermasalah</h6>
        <a href="<?= Helper::baseUrl('inspeksi?status=kotor') ?>" class="small text-muted">Lihat semua</a>
      </div>
      <?php if (empty($masalah)): ?>
        <div class="alert alert-success mb-0 small"><i class="bi bi-check-circle me-1"></i>Semua area dalam kondisi baik.</div>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($masalah as $m):
          $sc = $m['status']==='kotor'?'danger':'warning';
        ?>
        <a href="<?= Helper::baseUrl("inspeksi/detail/{$m['inspeksi_id']}") ?>"
           class="list-group-item list-group-item-action px-0 py-2 border-0">
          <div class="d-flex align-items-start gap-2">
            <div class="flex-shrink-0 mt-1">
              <span class="badge bg-<?= $sc ?>"><?= number_format($m['skor_total'],0) ?></span>
            </div>
            <div class="flex-grow-1 min-width-0">
              <div class="fw-semibold small text-truncate"><?= Helper::e($m['ruangan_nama']) ?></div>
              <div class="text-muted" style="font-size:11px">
                <?= Helper::e($m['gedung_nama']) ?> &bull; <?= Helper::dateId($m['tanggal']) ?>
              </div>
              <?php if ($m['petugas_nama']): ?>
              <div class="text-muted" style="font-size:11px"><i class="bi bi-person me-1"></i><?= Helper::e($m['petugas_nama']) ?></div>
              <?php endif; ?>
            </div>
            <i class="bi bi-chevron-right text-muted small mt-1"></i>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Aktivitas terbaru -->
  <div class="col-lg-4">
    <div class="page-card h-100">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Aktivitas Terbaru</h6>
        <a href="<?= Helper::baseUrl('inspeksi') ?>" class="small text-muted">Semua</a>
      </div>
      <div class="list-group list-group-flush">
        <?php foreach ($aktivitas as $a):
          $sc = match($a['status']??''){'bersih'=>'success','kurang_bersih'=>'warning','kotor'=>'danger',default=>'secondary'};
        ?>
        <a href="<?= Helper::baseUrl("inspeksi/detail/{$a['id']}") ?>"
           class="list-group-item list-group-item-action px-0 py-2 border-0">
          <div class="d-flex align-items-center gap-2">
            <div class="flex-shrink-0 rounded-circle bg-<?= $sc ?> bg-opacity-10 d-flex align-items-center justify-content-center"
                 style="width:32px;height:32px;font-size:12px;font-weight:700;color:var(--bs-<?= $sc ?>)">
              <?= $a['skor_total'] ? (int)$a['skor_total'] : '?' ?>
            </div>
            <div class="flex-grow-1 min-width-0">
              <div class="fw-semibold small text-truncate"><?= Helper::e($a['ruangan_nama']) ?></div>
              <div class="text-muted" style="font-size:11px">
                <?= Helper::e($a['inspected_nama']) ?> &bull; <?= Helper::dateId($a['tanggal']) ?>
              </div>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<!-- ── Bar chart per Unit ── -->
<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="page-card">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2 text-success"></i>Rata Skor per Unit (30 Hari)</h6>
      </div>
      <div style="position:relative;height:180px">
        <canvas id="chartUnit"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="page-card">
      <div class="page-card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-grid me-2 text-primary"></i>Persentase Ruangan Terinspeksi</h6>
      </div>
      <div class="d-flex align-items-center justify-content-center gap-4 py-3">
        <?php
        $pct = $kartu['total_ruangan'] > 0
             ? round($kartu['ruangan_terInspeksi'] / $kartu['total_ruangan'] * 100)
             : 0;
        ?>
        <div style="position:relative;width:130px;height:130px">
          <canvas id="chartDonut"></canvas>
          <div class="position-absolute top-50 start-50 translate-middle text-center">
            <div class="fw-bold fs-4"><?= $pct ?>%</div>
            <div class="text-muted" style="font-size:11px">Tercakup</div>
          </div>
        </div>
        <div>
          <div class="mb-2">
            <div class="small text-muted">Terinspeksi (30 hr)</div>
            <div class="fw-bold fs-5 text-success"><?= $kartu['ruangan_terInspeksi'] ?></div>
          </div>
          <div>
            <div class="small text-muted">Total Ruangan</div>
            <div class="fw-bold fs-5"><?= $kartu['total_ruangan'] ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tombol refresh manual -->
<div class="text-end mb-2">
  <button class="btn btn-sm btn-outline-secondary" onclick="refreshDashboard()">
    <i class="bi bi-arrow-clockwise me-1"></i>Refresh Data
  </button>
  <span id="lastRefresh" class="small text-muted ms-2"></span>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Segoe UI', sans-serif";
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#6b7280';

let chartTren, chartPie, chartUnit, chartDonut;
let trenData = [], currentChartType = 'bar';

/* ── Init on load ────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  initCharts();
  loadStats();
  updateLastRefresh();
  // Auto-refresh setiap 5 menit
  setInterval(loadStats, 300000);
});

/* ── Fetch stats ─────────────────────────────────────── */
function loadStats() {
  fetch('<?= Helper::baseUrl('dashboard/stats') ?>', {
    headers: {'X-Requested-With': 'XMLHttpRequest'}
  })
  .then(r => r.json())
  .then(data => {
    updateTrenChart(data.tren);
    updatePieChart(data.distribusi);
    updateUnitChart(data.unit);
    updateDonut(data.heatmap);
    updateLastRefresh();
  })
  .catch(e => console.warn('Dashboard refresh failed:', e));
}

function refreshDashboard() {
  document.getElementById('lastRefresh').textContent = 'Memuat...';
  loadStats();
}

function updateLastRefresh() {
  const now = new Date();
  document.getElementById('lastRefresh').textContent =
    'Diperbarui: ' + now.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'});
}

/* ── Init charts dengan data kosong ──────────────────── */
function initCharts() {
  // Tren
  chartTren = new Chart(document.getElementById('chartTren'), {
    type: 'bar',
    data: { labels:[], datasets:[
      { label:'Bersih',       data:[], backgroundColor:'rgba(34,197,94,.7)',  borderRadius:4 },
      { label:'Kurang Bersih',data:[], backgroundColor:'rgba(245,158,11,.7)', borderRadius:4 },
      { label:'Kotor',        data:[], backgroundColor:'rgba(239,68,68,.7)',  borderRadius:4 },
    ]},
    options: { responsive:true, maintainAspectRatio:false,
      scales:{ x:{stacked:true,ticks:{maxTicksLimit:10}}, y:{stacked:true,beginAtZero:true} },
      plugins:{ legend:{position:'bottom',labels:{boxWidth:12,padding:10}} } }
  });

  // Pie
  chartPie = new Chart(document.getElementById('chartPie'), {
    type: 'doughnut',
    data: { labels:['Bersih','Kurang Bersih','Kotor'], datasets:[{
      data:[0,0,0], backgroundColor:['#22c55e','#f59e0b','#ef4444'],
      borderWidth:2, borderColor:'#fff',
    }]},
    options:{ responsive:true, maintainAspectRatio:false, cutout:'65%',
      plugins:{ legend:{display:false} } }
  });

  // Unit bar
  chartUnit = new Chart(document.getElementById('chartUnit'), {
    type: 'bar',
    data: { labels:[], datasets:[{
      label:'Rata Skor', data:[], backgroundColor:['#22c55e','#3b82f6','#f59e0b','#06b6d4'],
      borderRadius:6,
    }]},
    options:{ responsive:true, maintainAspectRatio:false,
      scales:{ y:{beginAtZero:true,max:100} },
      plugins:{ legend:{display:false} } }
  });

  // Donut coverage
  const pct = <?= $pct ?>;
  chartDonut = new Chart(document.getElementById('chartDonut'), {
    type: 'doughnut',
    data:{ labels:['Terinspeksi','Belum'], datasets:[{
      data:[pct, 100-pct],
      backgroundColor:['#22c55e','#e5e7eb'],
      borderWidth:0,
    }]},
    options:{ responsive:true, maintainAspectRatio:false, cutout:'72%',
      plugins:{ legend:{display:false}, tooltip:{enabled:false} } }
  });
}

/* ── Update chart data ───────────────────────────────── */
function updateTrenChart(tren) {
  if (!tren || !tren.length) return;
  trenData = tren;
  chartTren.data.labels                = tren.map(t => t.tanggal.slice(5));
  chartTren.data.datasets[0].data      = tren.map(t => t.bersih||0);
  chartTren.data.datasets[1].data      = tren.map(t => t.kurang_bersih||0);
  chartTren.data.datasets[2].data      = tren.map(t => t.kotor||0);
  chartTren.update('none');
}

function updatePieChart(dist) {
  if (!dist || !dist.length) return;
  const map = {};
  dist.forEach(d => map[d.status] = parseInt(d.total));
  chartPie.data.datasets[0].data = [
    map['bersih']||0, map['kurang_bersih']||0, map['kotor']||0
  ];
  chartPie.update('none');
  document.getElementById('statBersih').textContent = map['bersih']||0;
  document.getElementById('statKurang').textContent = map['kurang_bersih']||0;
  document.getElementById('statKotor').textContent  = map['kotor']||0;
}

function updateUnitChart(units) {
  if (!units || !units.length) return;
  const colors = { smp:'#22c55e', smk:'#3b82f6', yayasan:'#f59e0b', masjid:'#06b6d4' };
  chartUnit.data.labels                = units.map(u => u.unit.toUpperCase());
  chartUnit.data.datasets[0].data      = units.map(u => parseFloat(u.avg_skor)||0);
  chartUnit.data.datasets[0].backgroundColor = units.map(u => colors[u.unit]||'#94a3b8');
  chartUnit.update('none');
}

function updateDonut(heatmap) {
  // Hitung coverage dari heatmap
  if (!heatmap) return;
  const inspected = heatmap.filter(h => h.total > 0).length;
  const total     = heatmap.length || 1;
  const pct       = Math.round(inspected/total*100);
  chartDonut.data.datasets[0].data = [pct, 100-pct];
  chartDonut.update('none');
}

/* ── Chart type toggle ───────────────────────────────── */
function switchChart(type, btn) {
  document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  currentChartType = type;
  chartTren.destroy();
  chartTren = new Chart(document.getElementById('chartTren'), {
    type: type,
    data: {
      labels: trenData.map(t => t.tanggal.slice(5)),
      datasets: [
        { label:'Bersih',       data: trenData.map(t=>t.bersih||0),
          backgroundColor:'rgba(34,197,94,.7)',  borderColor:'#22c55e', fill: type==='line', tension:.4, borderRadius:4 },
        { label:'Kurang Bersih',data: trenData.map(t=>t.kurang_bersih||0),
          backgroundColor:'rgba(245,158,11,.7)', borderColor:'#f59e0b', fill: type==='line', tension:.4, borderRadius:4 },
        { label:'Kotor',        data: trenData.map(t=>t.kotor||0),
          backgroundColor:'rgba(239,68,68,.7)',  borderColor:'#ef4444', fill: type==='line', tension:.4, borderRadius:4 },
      ]
    },
    options:{ responsive:true, maintainAspectRatio:false,
      scales:{ x:{stacked:type==='bar',ticks:{maxTicksLimit:10}}, y:{stacked:type==='bar',beginAtZero:true} },
      plugins:{ legend:{position:'bottom',labels:{boxWidth:12,padding:10}} } }
  });
}
</script>
