<?php
class RuanganController extends Controller
{
    private RuanganModel $model;
    private LantaiModel  $lantaiModel;
    private GedungModel  $gedungModel;
    private UserModel    $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->model       = new RuanganModel();
        $this->lantaiModel = new LantaiModel();
        $this->gedungModel = new GedungModel();
        $this->userModel   = new UserModel();
        AuthMiddleware::supervisorUp();
    }

    public function index(): void
    {
        $ruangan   = $this->model->allWithRelasi();
        $pageTitle = 'Master Ruangan';
        $this->layout('master.ruangan.index', compact('ruangan', 'pageTitle'));
    }

    public function create(): void
    {
        $gedung    = $this->gedungModel->findAll('unit, nama');
        $petugas   = $this->userModel->allActive('petugas');
        $tipeList  = StandarModel::tipeList();
        $pageTitle = 'Tambah Ruangan';
        $this->layout('master.ruangan.form', compact('gedung', 'petugas', 'tipeList', 'pageTitle'));
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->validate();
        if (isset($data['error'])) { Session::flash('error', $data['error']); $this->redirect('ruangan/create'); }
        if ($this->model->kodeExists($data['kode'])) { Session::flash('error', 'Kode ruangan sudah digunakan.'); $this->redirect('ruangan/create'); }
        $id = $this->model->insert($data);
        $this->logActivity('create', 'ruangan', $id, "Tambah ruangan: {$data['nama']}");
        Session::flash('success', 'Ruangan berhasil ditambahkan.');
        $this->redirect('ruangan');
    }

    public function edit(string $id): void
    {
        $ruangan   = $this->model->findWithRelasi((int)$id);
        if (!$ruangan) { Session::flash('error', 'Ruangan tidak ditemukan.'); $this->redirect('ruangan'); }
        $gedung    = $this->gedungModel->findAll('unit, nama');
        $lantai    = $this->lantaiModel->byGedung($ruangan['gedung_id']);
        $petugas   = $this->userModel->allActive('petugas');
        $tipeList  = StandarModel::tipeList();
        $pageTitle = 'Edit Ruangan';
        $this->layout('master.ruangan.form', compact('ruangan', 'gedung', 'lantai', 'petugas', 'tipeList', 'pageTitle'));
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();
        $data = $this->validate();
        if (isset($data['error'])) { Session::flash('error', $data['error']); $this->redirect("ruangan/edit/{$id}"); }
        if ($this->model->kodeExists($data['kode'], (int)$id)) { Session::flash('error', 'Kode ruangan sudah digunakan.'); $this->redirect("ruangan/edit/{$id}"); }
        $this->model->update((int)$id, $data);
        $this->logActivity('update', 'ruangan', (int)$id, "Edit ruangan: {$data['nama']}");
        Session::flash('success', 'Ruangan berhasil diperbarui.');
        $this->redirect('ruangan');
    }

    public function delete(): void
    {
        $this->verifyCsrf();
        AuthMiddleware::adminOnly();
        $id = (int)$this->post('id');
        $r  = $this->model->find($id);
        if (!$r) { Session::flash('error', 'Data tidak ditemukan.'); $this->redirect('ruangan'); }
        $this->model->update($id, ['is_active' => 0]);
        $this->logActivity('delete', 'ruangan', $id, "Hapus ruangan: {$r['nama']}");
        Session::flash('success', 'Ruangan berhasil dihapus.');
        $this->redirect('ruangan');
    }

    private function validate(): array
    {
        $lantaiId  = (int)$this->post('lantai_id');
        $kode      = strtoupper(trim($this->post('kode', '')));
        $nama      = trim($this->post('nama', ''));
        $tipe      = $this->post('tipe', 'lainnya');
        $zona      = trim($this->post('zona', ''));
        $assigned  = ($this->post('assigned_user_id')) ?: null;
        $luas      = ($this->post('luas_m2')) ?: null;
        $ket       = trim($this->post('keterangan', ''));
        $tipeValid = array_keys(StandarModel::tipeList());
        if (!$lantaiId || !$kode || !$nama || !in_array($tipe, $tipeValid)) return ['error' => 'Data tidak lengkap atau tidak valid.'];
        return [
                    'lantai_id'        => $lantaiId,
                    'kode'             => $kode,
                    'nama'             => $nama,
                    'tipe'             => $tipe,
                    'zona'             => $zona,
                    'assigned_user_id' => $assigned,
                    'luas_m2'          => $luas,
                    'keterangan'       => $ket,
                    'is_active'        => 1,
                ];
    }
}
