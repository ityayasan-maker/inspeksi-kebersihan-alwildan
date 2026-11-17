<?php
class DashboardController extends Controller
{
    private DashboardModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new DashboardModel();
        AuthMiddleware::check();
    }

    /* ── GET /dashboard ──────────────────────────────────── */
    public function index(): void
    {
        $kartu     = $this->model->statsKartu();
        $aktivitas = $this->model->aktivitasTerbaru(8);
        $masalah   = $this->model->areaBermasalah(5);
        $ranking   = $this->model->rankingPetugas(5);
        $pageTitle = 'Dashboard';
        $this->layout('dashboard.index', compact(
            'kartu','aktivitas','masalah','ranking','pageTitle'
        ));
    }

    /* ── GET /dashboard/stats (AJAX) ─────────────────────── */
    public function stats(): void
    {
        $this->json([
            'kartu'       => $this->model->statsKartu(),
            'tren'        => $this->model->trenHarian(30),
            'distribusi'  => $this->model->distribusiStatus(),
            'unit'        => $this->model->distribusiUnit(),
            'heatmap'     => $this->model->heatmapRuangan(),
        ]);
    }

    /* ── GET /dashboard/ranking (AJAX) ───────────────────── */
    public function ranking(): void
    {
        $this->json($this->model->rankingPetugas(10));
    }

    /* ── GET /dashboard/area-masalah (AJAX) ──────────────── */
    public function areaMasalah(): void
    {
        $this->json($this->model->areaBermasalah(10));
    }
}
