<?php
class LaporanController extends Controller
{
    private LaporanModel  $model;
    private InspeksiModel $inspeksiModel;

    public function __construct()
    {
        parent::__construct();
        $this->model        = new LaporanModel();
        $this->inspeksiModel= new InspeksiModel();
        AuthMiddleware::check();
    }

    /* ── GET /laporan ────────────────────────────────────── */
    public function index(): void
    {
        $filters  = $this->buildFilters();
        $rows     = $this->model->getFiltered($filters);
        $ringkasan= $this->model->getRingkasan($rows);

        // Data untuk dropdown filter
        $gedung   = (new GedungModel())->findAll('unit, nama');
        $petugas  = (new UserModel())->allActive();
        $ruangan  = (new RuanganModel())->allWithRelasi();

        $pageTitle= 'Laporan Inspeksi';
        $this->layout('laporan.index', compact(
            'rows','filters','ringkasan','gedung','petugas','ruangan','pageTitle'
        ));
    }

    /* ── GET /laporan/export-excel ───────────────────────── */
    public function exportExcel(): void
    {
        AuthMiddleware::supervisorUp();
        $filters = $this->buildFilters();
        $rows    = $this->model->getFiltered($filters);

        // Generate CSV (kompatibel Excel tanpa library)
        $filename = 'laporan_inspeksi_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        // BOM untuk Excel agar UTF-8 terbaca
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');

        // Header kolom
        fputcsv($out, [
            'No','ID','Tanggal','Gedung','Unit','Lantai','Ruangan','Kode Ruangan',
            'Tipe Ruangan','Petugas PJ','Diinspeksi Oleh',
            'Skor','Status','Waktu Mulai','Catatan Umum'
        ], ';');

        $no = 1;
        $tipeList = StandarModel::tipeList();
        foreach ($rows as $r) {
            fputcsv($out, [
                $no++,
                $r['id'],
                $r['tanggal'],
                $r['gedung_nama'],
                strtoupper($r['gedung_unit']),
                $r['lantai_nama'],
                $r['ruangan_nama'],
                $r['ruangan_kode'],
                $tipeList[$r['ruangan_tipe']] ?? $r['ruangan_tipe'],
                $r['assigned_nama']  ?? '-',
                $r['inspected_nama'] ?? '-',
                $r['skor_total'] !== null ? number_format((float)$r['skor_total'],1,',','') : '-',
                Helper::statusLabel($r['status'] ?? ''),
                $r['waktu_mulai'] ?? '-',
                $r['catatan_umum'] ?? '-',
            ], ';');
        }

        fclose($out);
        exit;
    }

    /* ── GET /laporan/export-pdf ─────────────────────────── */
    public function exportPdf(): void
    {
        AuthMiddleware::supervisorUp();
        $filters  = $this->buildFilters();
        $rows     = $this->model->getFiltered($filters);
        $ringkasan= $this->model->getRingkasan($rows);
        $tipeList = StandarModel::tipeList();

        header('Content-Type: text/html; charset=UTF-8');
        // Render HTML print-ready — user tinggal Ctrl+P dan pilih Save as PDF
        $this->view('laporan.export_pdf', compact('rows','filters','ringkasan','tipeList'));
        exit;
    }

    /* ── GET /laporan/print?id=X ─────────────────────────── */
    public function print(): void
    {
        $id = (int)$this->get('id');
        if (!$id) { $this->redirect('laporan'); }

        $inspeksi = $this->inspeksiModel->findDetail($id);
        if (!$inspeksi) { Session::flash('error','Inspeksi tidak ditemukan.'); $this->redirect('inspeksi'); }

        $details  = $this->model->getDetailInspeksi($id);
        $tipeList = StandarModel::tipeList();

        header('Content-Type: text/html; charset=UTF-8');
        $this->view('laporan.print', compact('inspeksi','details','tipeList'));
        exit;
    }

    /* ── Helper ──────────────────────────────────────────── */
    private function buildFilters(): array
    {
        return [
            'tanggal_dari'   => $this->get('tanggal_dari', date('Y-m-01')),
            'tanggal_sampai' => $this->get('tanggal_sampai', date('Y-m-d')),
            'status'         => $this->get('status', ''),
            'gedung_id'      => $this->get('gedung_id', ''),
            'ruangan_id'     => $this->get('ruangan_id', ''),
            'petugas_id'     => $this->get('petugas_id', ''),
            'unit'           => $this->get('unit', ''),
            'skor_min'       => $this->get('skor_min', ''),
            'skor_max'       => $this->get('skor_max', ''),
        ];
    }
}
