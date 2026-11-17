<?php
class GedungController extends Controller
{
    private GedungModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new GedungModel();
        AuthMiddleware::supervisorUp();
    }

    public function index(): void
    {
        $gedung    = $this->model->allWithCount();
        $pageTitle = 'Master Gedung';
        $this->layout('master.gedung.index', compact('gedung', 'pageTitle'));
    }

    public function create(): void
    {
        $pageTitle = 'Tambah Gedung';
        $this->layout('master.gedung.form', compact('pageTitle'));
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->validate();
        if (is_array($data) && isset($data['error'])) {
            Session::flash('error', $data['error']);
            $this->redirect('gedung/create');
        }
        if ($this->model->kodeExists($data['kode'])) {
            Session::flash('error', 'Kode gedung sudah digunakan.');
            $this->redirect('gedung/create');
        }
        $id = $this->model->insert($data);
        $this->logActivity('create', 'gedung', $id, "Tambah gedung: {$data['nama']}");
        Session::flash('success', 'Gedung berhasil ditambahkan.');
        $this->redirect('gedung');
    }

    public function edit(string $id): void
    {
        $gedung    = $this->model->find((int)$id);
        if (!$gedung) { Session::flash('error', 'Gedung tidak ditemukan.'); $this->redirect('gedung'); }
        $pageTitle = 'Edit Gedung';
        $this->layout('master.gedung.form', compact('gedung', 'pageTitle'));
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();
        $data = $this->validate();
        if (isset($data['error'])) { Session::flash('error', $data['error']); $this->redirect("gedung/edit/{$id}"); }
        if ($this->model->kodeExists($data['kode'], (int)$id)) {
            Session::flash('error', 'Kode gedung sudah digunakan.'); $this->redirect("gedung/edit/{$id}");
        }
        $this->model->update((int)$id, $data);
        $this->logActivity('update', 'gedung', (int)$id, "Edit gedung: {$data['nama']}");
        Session::flash('success', 'Gedung berhasil diperbarui.');
        $this->redirect('gedung');
    }

    public function delete(): void
    {
        $this->verifyCsrf();
        AuthMiddleware::adminOnly();
        $id = (int)$this->post('id');
        $gedung = $this->model->find($id);
        if (!$gedung) { Session::flash('error', 'Gedung tidak ditemukan.'); $this->redirect('gedung'); }
        $this->model->update($id, ['is_active' => 0]);
        $this->logActivity('delete', 'gedung', $id, "Hapus gedung: {$gedung['nama']}");
        Session::flash('success', 'Gedung berhasil dihapus.');
        $this->redirect('gedung');
    }

    private function validate(): array
    {
        $kode  = strtoupper(trim($this->post('kode', '')));
        $nama  = trim($this->post('nama', ''));
        $unit  = strtolower(trim($this->post('unit', '')));
        $ket   = trim($this->post('keterangan', ''));
        if (!$kode || !$nama || !$unit) {
            return ['error' => 'Semua field wajib diisi dengan benar.'];
        }
        return ['kode' => $kode, 'nama' => $nama, 'unit' => $unit, 'keterangan' => $ket, 'is_active' => 1];
    }
}
