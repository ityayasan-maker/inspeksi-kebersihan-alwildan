<?php
class InspeksiController extends Controller
{
    private InspeksiModel       $model;
    private InspeksiDetailModel $detailModel;
    private ChecklistModel      $checklistModel;
    private RuanganModel        $ruanganModel;
    private UserModel           $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->model          = new InspeksiModel();
        $this->detailModel    = new InspeksiDetailModel();
        $this->checklistModel = new ChecklistModel();
        $this->ruanganModel   = new RuanganModel();
        $this->userModel      = new UserModel();
        AuthMiddleware::check();
    }

    /* ── GET /inspeksi ───────────────────────────────────── */
    public function index(): void
    {
        $role    = Session::role();
        $userId  = Session::get('user_id');
        $page    = max(1, (int)($this->get('page', 1)));

        $filters = [
            'status'          => $this->get('status', ''),
            'tanggal_dari'    => $this->get('tanggal_dari', ''),
            'tanggal_sampai'  => $this->get('tanggal_sampai', ''),
        ];
        // Petugas hanya lihat inspeksi terkait dirinya
        if ($role === 'petugas') {
            $filters['user_id_filter'] = $userId;
        }
        if ($this->get('ruangan_id')) {
            $filters['ruangan_id'] = (int)$this->get('ruangan_id');
        }

        $paginator = $this->model->allWithRelasi($filters, $page);
        $pageTitle = 'Daftar Inspeksi';
        $this->layout('inspeksi.index', compact('paginator', 'filters', 'pageTitle'));
    }

    /* ── GET /inspeksi/create ────────────────────────────── */
    public function create(): void
    {
        $ruangan           = $this->ruanganModel->allWithRelasi();
        $gedungList        = (new GedungModel())->allWithCount();
        $petugas           = $this->userModel->allActive();
        $selectedRuanganId = (int)$this->get('ruangan_id', 0);
        $selectedSesi      = $this->get('sesi', '');
        $pageTitle         = 'Form Inspeksi Baru';
        $this->layout('inspeksi.create', compact('ruangan', 'gedungList', 'petugas', 'selectedRuanganId', 'selectedSesi', 'pageTitle'));
    }

    /* ── POST /inspeksi/create ───────────────────────────── */
    public function store(): void
    {
        $this->verifyCsrf();

        $ruanganId   = (int)$this->post('ruangan_id');
        $tanggal     = $this->post('tanggal', date('Y-m-d'));
        $waktuMulai  = $this->post('waktu_mulai', '') ?: date('H:i:s');
        $sesi        = $this->post('sesi', '');
        if (!$sesi) {
            $curH = (int)date('H');
            $sesi = ($curH <= 10) ? 'pagi' : (($curH <= 15) ? 'siang' : 'insidental');
        }
        $catatanUmum = trim($this->post('catatan_umum', ''));
        $isDraft     = (int)$this->post('is_draft', 0);

        if (!$ruanganId) {
            Session::flash('error', 'Pilih ruangan terlebih dahulu.');
            $this->redirect('inspeksi/create');
        }

        // assigned_user_id: ambil dari form (dropdown PJ), fallback ke ruangan jika tidak dipilih
        $assignedPost = (int)$this->post('assigned_user_id');
        $ruangan = $this->ruanganModel->find($ruanganId);
        $assignedId = $assignedPost > 0 ? $assignedPost : ($ruangan['assigned_user_id'] ?? null);

        // inspected_by: selalu user yang sedang login (penilai)
        $inspectedById = Session::get('user_id');

        // Buat nomor inspeksi unik
        $nomorInspeksi = 'INSP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        // Buat header inspeksi
        $inspeksiId = $this->model->insert([
            'nomor_inspeksi'      => $nomorInspeksi,
            'ruangan_id'          => $ruanganId,
            'assigned_user_id'    => $assignedId,
            'assigned_petugas_id' => $assignedId,
            'inspected_by_id'     => $inspectedById,
            'tanggal'             => $tanggal,
            'waktu_mulai'         => $waktuMulai,
            'sesi'                => $sesi,
            'catatan'             => $catatanUmum,
            'catatan_umum'        => $catatanUmum,
            'is_draft'            => $isDraft,
        ]);

        // Simpan foto kondisi ruangan utama jika ada
        if (!empty($_FILES['foto_ruangan']['name']) && ($_FILES['foto_ruangan']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $uploader = new Uploader();
            $resFotoRuangan = $uploader->uploadInspeksiFoto($_FILES['foto_ruangan'], $inspeksiId, 'ruangan');
            if ($resFotoRuangan['success']) {
                try {
                    $this->model->update($inspeksiId, ['foto_ruangan' => $resFotoRuangan['filename']]);
                } catch (\Throwable $e) {
                    error_log("[InspeksiController] Gagal update foto_ruangan: " . $e->getMessage());
                }
            } else {
                error_log("[InspeksiController] Upload foto_ruangan gagal: " . ($resFotoRuangan['error'] ?? ''));
            }
        }

        // Simpan detail checklist
        $this->saveDetails($inspeksiId, $ruanganId);

        // Hitung skor otomatis (skip jika draft)
        if (!$isDraft) {
            $this->model->recalcScore($inspeksiId);
            $this->triggerNotifikasi($inspeksiId);
        }

        $this->logActivity('create', 'inspeksi', $inspeksiId,
            "Inspeksi ruangan_id={$ruanganId}, draft={$isDraft}");

        Session::flash('success', $isDraft
            ? 'Draft inspeksi disimpan.'
            : 'Inspeksi berhasil disimpan.');

        $this->redirect("inspeksi/detail/{$inspeksiId}");
    }

    /* ── GET /inspeksi/detail/:id ────────────────────────── */
    public function detail(string $id): void
    {
        $inspeksi = $this->model->findDetail((int)$id);
        if (!$inspeksi) { Session::flash('error', 'Inspeksi tidak ditemukan.'); $this->redirect('inspeksi'); }
        $this->authorizeView($inspeksi);

        $details   = $this->detailModel->byInspeksi((int)$id);
        $history   = $this->model->recentByRuangan($inspeksi['ruangan_id']);
        $tipeList  = StandarModel::tipeList();
        $frekList  = StandarModel::frekuensiList();
        $pageTitle = 'Detail Inspeksi #' . $id;
        $this->layout('inspeksi.detail', compact(
            'inspeksi','details','history','tipeList','frekList','pageTitle'
        ));
    }

    /* ── GET /inspeksi/edit/:id ──────────────────────────── */
    public function edit(string $id): void
    {
        $inspeksi = $this->model->findDetail((int)$id);
        if (!$inspeksi) { Session::flash('error', 'Inspeksi tidak ditemukan.'); $this->redirect('inspeksi'); }
        $this->authorizeEdit($inspeksi);

        $details   = $this->detailModel->byInspeksi((int)$id);
        $ruangan   = $this->ruanganModel->find($inspeksi['ruangan_id']);
        $checklist = $this->checklistModel->getEffectiveChecklist($inspeksi['ruangan_id']);
        $petugas   = $this->userModel->allActive();
        $pageTitle = 'Edit Inspeksi #' . $id;
        $this->layout('inspeksi.edit', compact(
            'inspeksi','details','ruangan','checklist','petugas','pageTitle'
        ));
    }

    /* ── POST /inspeksi/edit/:id ─────────────────────────── */
    public function update(string $id): void
    {
        $this->verifyCsrf();
        $inspeksi = $this->model->find((int)$id);
        if (!$inspeksi) { Session::flash('error', 'Inspeksi tidak ditemukan.'); $this->redirect('inspeksi'); }
        $this->authorizeEdit($inspeksi);

        $isDraft      = (int)$this->post('is_draft', 0);
        $assignedPost = (int)$this->post('assigned_user_id');

        $this->model->update((int)$id, [
            'assigned_user_id' => $assignedPost > 0 ? $assignedPost : ($inspeksi['assigned_user_id'] ?? null),
            'tanggal'          => $this->post('tanggal', $inspeksi['tanggal']),
            'waktu_mulai'      => $this->post('waktu_mulai', '') ?: null,
            'catatan_umum'     => trim($this->post('catatan_umum', '')),
            'is_draft'         => $isDraft,
        ]);

        $this->saveDetails((int)$id, $inspeksi['ruangan_id']);

        if (!$isDraft) {
            $this->model->recalcScore((int)$id);
            $this->triggerNotifikasi((int)$id);
        }

        $this->logActivity('update', 'inspeksi', (int)$id);
        Session::flash('success', 'Inspeksi berhasil diperbarui.');
        $this->redirect("inspeksi/detail/{$id}");
    }

    /* ── POST /inspeksi/delete ───────────────────────────── */
    public function delete(): void
    {
        $this->verifyCsrf();
        AuthMiddleware::supervisorUp();
        $id = (int)$this->post('id');
        $this->detailModel->deleteByInspeksi($id);
        $this->model->delete($id);
        $this->logActivity('delete', 'inspeksi', $id);
        Session::flash('success', 'Inspeksi berhasil dihapus.');
        $this->redirect('inspeksi');
    }

    /* ── GET /inspeksi/checklist (AJAX) ──────────────────── */
    public function getChecklist(): void
    {
        $ruanganId = (int)$this->get('ruangan_id');
        if (!$ruanganId) { $this->json([]); return; }

        $checklist = $this->checklistModel->getEffectiveChecklist($ruanganId);
        $ruangan   = $this->ruanganModel->find($ruanganId);
        $this->json([
            'checklist'         => $checklist,
            'assigned_user_id'  => $ruangan['assigned_user_id'] ?? null,
            'ruangan_tipe'      => $ruangan['tipe'] ?? '',
        ]);
    }

    /* ── POST /inspeksi/upload-foto ──────────────────────── */
    public function uploadFoto(): void
    {
        (new UploadController())->inspeksiFoto();
    }

    /* ── Helpers ─────────────────────────────────────────── */

    /** Simpan/update detail checklist dari POST data beserta file foto */
    private function saveDetails(int $inspeksiId, int $ruanganId): void
    {
        $kondisiArr = $_POST['kondisi'] ?? [];    // [standar_id => kondisi]
        $catatanArr = $_POST['catatan'] ?? [];    // [standar_id => catatan]

        if (!$kondisiArr) return;

        // Ambil foto yang sudah ada (jika ini edit/update)
        $existingFotos = [];
        $currentDetails = $this->detailModel->byInspeksi($inspeksiId);
        foreach ($currentDetails as $cd) {
            if (!empty($cd['foto'])) {
                $existingFotos[$cd['standar_id']] = $cd['foto'];
            }
        }

        $uploader = new Uploader();
        $files = $_FILES['foto'] ?? [];

        foreach ($kondisiArr as $standarId => $kondisi) {
            $standarId = (int)$standarId;
            $kondisi   = in_array($kondisi, ['bersih','kurang_bersih','kotor'])
                         ? $kondisi : 'kotor';
            $skor      = $this->detailModel->skorFromKondisi($kondisi);
            $catatan   = trim($catatanArr[$standarId] ?? '');
            $foto      = $existingFotos[$standarId] ?? null;

            // Jika ada file foto yang diunggah untuk item checklist ini
            if (
                !empty($files['name'][$standarId]) &&
                isset($files['error'][$standarId]) &&
                $files['error'][$standarId] === UPLOAD_ERR_OK
            ) {
                $singleFile = [
                    'name'     => $files['name'][$standarId],
                    'type'     => $files['type'][$standarId] ?? 'image/jpeg',
                    'tmp_name' => $files['tmp_name'][$standarId],
                    'error'    => $files['error'][$standarId],
                    'size'     => $files['size'][$standarId] ?? 0,
                ];
                $uploadRes = $uploader->uploadInspeksiFoto($singleFile, $inspeksiId, "item_{$standarId}");
                if ($uploadRes['success']) {
                    if ($foto && $foto !== $uploadRes['filename']) {
                        $uploader->delete("inspeksi/{$inspeksiId}/{$foto}");
                    }
                    $foto = $uploadRes['filename'];
                } else {
                    error_log("[InspeksiController] Upload checklist foto item {$standarId} gagal: " . ($uploadRes['error'] ?? ''));
                }
            }

            $detailPayload = [
                'kondisi' => $kondisi,
                'skor'    => $skor,
                'catatan' => $catatan ?: null,
            ];
            if ($foto !== null) {
                $detailPayload['foto'] = $foto;
            }

            $this->detailModel->upsert($inspeksiId, $standarId, $detailPayload);
        }
    }

    /** Kirim notifikasi jika skor di bawah threshold */
    private function triggerNotifikasi(int $inspeksiId): void
    {
        try {
            (new ThresholdChecker())->checkAfterInspeksi($inspeksiId);
        } catch (\Throwable $e) {
            error_log("[InspeksiController] triggerNotifikasi: " . $e->getMessage());
        }
    }

    private function authorizeView(array $inspeksi): void
    {
        if (Session::role() === 'petugas') {
            $uid = Session::get('user_id');
            if ($inspeksi['assigned_user_id'] != $uid && $inspeksi['inspected_by_id'] != $uid) {
                $this->redirect('inspeksi');
            }
        }
    }

    private function authorizeEdit(array $inspeksi): void
    {
        $role = Session::role();
        if ($role === 'petugas' && $inspeksi['inspected_by_id'] != Session::get('user_id')) {
            Session::flash('error', 'Anda tidak dapat mengedit inspeksi ini.');
            $this->redirect('inspeksi');
        }
        if ($role === 'petugas' && !$inspeksi['is_draft']) {
            Session::flash('error', 'Inspeksi yang sudah final tidak dapat diedit.');
            $this->redirect("inspeksi/detail/{$inspeksi['id']}");
        }
    }
}
