<?php
class StandarController extends Controller
{
    private StandarModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new StandarModel();
        AuthMiddleware::supervisorUp();
    }

    public function index(): void
    {
        $grouped   = $this->model->allGrouped();
        $tipeList  = StandarModel::tipeList();
        $pageTitle = 'Standar Kebersihan';
        $this->layout('master.standar.index', compact('grouped', 'tipeList', 'pageTitle'));
    }

    public function create(): void
    {
        $tipeList  = StandarModel::tipeList();
        $frekList  = StandarModel::frekuensiList();
        $pageTitle = 'Tambah Standar';
        $this->layout('master.standar.form', compact('tipeList', 'frekList', 'pageTitle'));
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->validate();
        if (isset($data['error'])) { Session::flash('error', $data['error']); $this->redirect('standar/create'); }
        $data['no_urut'] = $this->model->nextUrut($data['tipe_ruangan']);
        $id = $this->model->insert($data);
        $this->logActivity('create', 'standar', $id, "Tambah standar: {$data['aspek']}");
        Session::flash('success', 'Standar kebersihan berhasil ditambahkan.');
        $this->redirect('standar');
    }

    public function edit(string $id): void
    {
        $standar   = $this->model->find((int)$id);
        if (!$standar) { Session::flash('error', 'Data tidak ditemukan.'); $this->redirect('standar'); }
        $tipeList  = StandarModel::tipeList();
        $frekList  = StandarModel::frekuensiList();
        $pageTitle = 'Edit Standar';
        $this->layout('master.standar.form', compact('standar', 'tipeList', 'frekList', 'pageTitle'));
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();
        $data = $this->validate();
        if (isset($data['error'])) { Session::flash('error', $data['error']); $this->redirect("standar/edit/{$id}"); }
        $this->model->update((int)$id, $data);
        $this->logActivity('update', 'standar', (int)$id);
        Session::flash('success', 'Standar kebersihan berhasil diperbarui.');
        $this->redirect('standar');
    }

    public function delete(): void
    {
        $this->verifyCsrf();
        AuthMiddleware::adminOnly();
        $id = (int)$this->post('id');
        $this->model->update($id, ['is_active' => 0]);
        $this->logActivity('delete', 'standar', $id);
        Session::flash('success', 'Standar berhasil dihapus.');
        $this->redirect('standar');
    }

    /** AJAX: GET /standar/by-tipe?tipe=kelas */
    public function byTipe(): void
    {
        AuthMiddleware::check();
        $tipe = $this->get('tipe', '');
        $this->json($this->model->byTipe($tipe));
    }

    private function validate(): array
    {
        $tipe  = $this->post('tipe_ruangan', '');
        $aspek = trim($this->post('aspek', ''));
        $desk  = trim($this->post('deskripsi', ''));
        $frek  = $this->post('frekuensi', 'harian');
        $valid = array_keys(StandarModel::tipeList());
        $vfrek = array_keys(StandarModel::frekuensiList());
        if (!$aspek || !in_array($tipe, $valid) || !in_array($frek, $vfrek)) return ['error' => 'Data tidak lengkap.'];
        return ['tipe_ruangan' => $tipe, 'aspek' => $aspek, 'deskripsi' => $desk, 'frekuensi' => $frek, 'is_active' => 1];
    }
}
