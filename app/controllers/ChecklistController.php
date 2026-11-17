<?php
/**
 * ChecklistController
 * Kelola custom checklist per ruangan
 * Routes:
 *   GET  /checklist/{ruangan_id}          → index (preview + manage)
 *   POST /checklist/{ruangan_id}/assign   → bulk assign
 *   POST /checklist/{ruangan_id}/add      → tambah 1 item
 *   POST /checklist/{ruangan_id}/toggle   → toggle aktif 1 item
 *   POST /checklist/{ruangan_id}/reset    → hapus override, fallback ke tipe
 *   GET  /checklist/{ruangan_id}/preview  → AJAX preview JSON
 *   POST /checklist/copy                  → copy dari ruangan lain
 */
class ChecklistController extends Controller
{
    private ChecklistModel $model;
    private RuanganModel   $ruanganModel;
    private StandarModel   $standarModel;

    public function __construct()
    {
        parent::__construct();
        $this->model        = new ChecklistModel();
        $this->ruanganModel = new RuanganModel();
        $this->standarModel = new StandarModel();
        AuthMiddleware::supervisorUp();
    }

    /* ── GET /checklist/{id} ─────────────────────────────── */
    public function index(string $ruanganId): void
    {
        $ruangan  = $this->ruanganModel->findWithRelasi((int)$ruanganId);
        if (!$ruangan) { Session::flash('error', 'Ruangan tidak ditemukan.'); $this->redirect('ruangan'); }

        $assigned   = $this->model->getByRuangan((int)$ruanganId);
        $available  = $this->model->getAvailable((int)$ruanganId);
        $effective  = $this->model->getEffectiveChecklist((int)$ruanganId);
        $hasOverride= $this->model->hasOverride((int)$ruanganId);
        $tipeList   = StandarModel::tipeList();
        $frekList   = StandarModel::frekuensiList();

        // Semua ruangan aktif untuk fitur copy
        $allRuangan = $this->ruanganModel->allWithRelasi();

        $pageTitle = 'Checklist: ' . $ruangan['nama'];
        $this->layout('master.checklist.index', compact(
            'ruangan','assigned','available','effective',
            'hasOverride','tipeList','frekList','allRuangan','pageTitle'
        ));
    }

    /* ── POST /checklist/{id}/assign ─────────────────────── */
    public function assign(string $ruanganId): void
    {
        $this->verifyCsrf();
        $ids = $_POST['standar_ids'] ?? [];
        $ids = array_map('intval', (array)$ids);
        $this->model->bulkAssign((int)$ruanganId, $ids);
        $this->logActivity('assign', 'checklist', (int)$ruanganId, count($ids) . ' item');
        Session::flash('success', count($ids) . ' checklist item berhasil disimpan.');
        $this->redirect("checklist/{$ruanganId}");
    }

    /* ── POST /checklist/{id}/add ────────────────────────── */
    public function add(string $ruanganId): void
    {
        $this->verifyCsrf();
        $standarId = (int)$this->post('standar_id');
        if (!$standarId) { Session::flash('error', 'Pilih standar terlebih dahulu.'); $this->redirect("checklist/{$ruanganId}"); }

        // Cek duplikasi
        $exists = (bool) $this->model->queryScalar(
            "SELECT COUNT(*) FROM ruangan_checklist WHERE ruangan_id=? AND standar_id=?",
            [(int)$ruanganId, $standarId]
        );
        if ($exists) {
            // Reaktifkan jika nonaktif
            $this->model->execute(
                "UPDATE ruangan_checklist SET is_active=1 WHERE ruangan_id=? AND standar_id=?",
                [(int)$ruanganId, $standarId]
            );
        } else {
            $this->model->insert([
                'ruangan_id' => (int)$ruanganId,
                'standar_id' => $standarId,
                'is_active'  => 1,
            ]);
        }
        $this->logActivity('add', 'checklist', (int)$ruanganId, "standar_id={$standarId}");
        Session::flash('success', 'Item checklist berhasil ditambahkan.');
        $this->redirect("checklist/{$ruanganId}");
    }

    /* ── POST /checklist/{id}/toggle ─────────────────────── */
    public function toggle(string $ruanganId): void
    {
        $this->verifyCsrf();
        $id = (int)$this->post('id');
        $this->model->toggle($id, (int)$ruanganId);
        Session::flash('success', 'Status item diperbarui.');
        $this->redirect("checklist/{$ruanganId}");
    }

    /* ── POST /checklist/{id}/reset ──────────────────────── */
    public function reset(string $ruanganId): void
    {
        $this->verifyCsrf();
        $this->model->resetOverride((int)$ruanganId);
        $this->logActivity('reset', 'checklist', (int)$ruanganId, 'Reset ke default tipe');
        Session::flash('success', 'Checklist direset ke standar default tipe ruangan.');
        $this->redirect("checklist/{$ruanganId}");
    }

    /* ── GET /checklist/{id}/preview (AJAX) ──────────────── */
    public function preview(string $ruanganId): void
    {
        $list = $this->model->getEffectiveChecklist((int)$ruanganId);
        $this->json([
            'ruangan_id'  => (int)$ruanganId,
            'total'       => count($list),
            'has_override'=> $this->model->hasOverride((int)$ruanganId),
            'items'       => $list,
        ]);
    }

    /* ── POST /checklist/copy ────────────────────────────── */
    public function copy(): void
    {
        $this->verifyCsrf();
        $source = (int)$this->post('source_ruangan_id');
        $target = (int)$this->post('target_ruangan_id');
        if (!$source || !$target || $source === $target) {
            Session::flash('error', 'Pilih ruangan sumber dan target yang berbeda.');
            $this->redirect('ruangan');
        }
        $n = $this->model->copyFrom($source, $target);
        $this->logActivity('copy', 'checklist', $target, "Copy dari ruangan_id={$source}, {$n} item");
        Session::flash('success', "{$n} item checklist berhasil dicopy.");
        $this->redirect("checklist/{$target}");
    }
}
