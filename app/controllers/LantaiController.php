<?php
class LantaiController extends Controller
{
    private LantaiModel $model;
    private GedungModel $gedungModel;

    public function __construct()
    {
        parent::__construct();
        $this->model       = new LantaiModel();
        $this->gedungModel = new GedungModel();
        AuthMiddleware::supervisorUp();
    }

    public function index(): void
    {
        $lantai    = $this->model->allWithGedung();
        $gedung    = $this->gedungModel->findAll('unit, nama');
        $pageTitle = 'Master Lantai';
        $this->layout('master.lantai.index', compact('lantai', 'gedung', 'pageTitle'));
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $gedungId = (int)$this->post('gedung_id');
        $nama     = trim($this->post('nama', ''));
        $urutan   = (int)$this->post('urutan', 1);
        if (!$gedungId || !$nama) { Session::flash('error', 'Data tidak lengkap.'); $this->redirect('lantai'); }
        $id = $this->model->insert(['gedung_id' => $gedungId, 'nama' => $nama, 'urutan' => $urutan]);
        $this->logActivity('create', 'lantai', $id, "Tambah lantai: {$nama}");
        Session::flash('success', 'Lantai berhasil ditambahkan.');
        $this->redirect('lantai');
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();
        $nama   = trim($this->post('nama', ''));
        $urutan = (int)$this->post('urutan', 1);
        if (!$nama) { Session::flash('error', 'Nama wajib diisi.'); $this->redirect('lantai'); }
        $this->model->update((int)$id, ['nama' => $nama, 'urutan' => $urutan]);
        $this->logActivity('update', 'lantai', (int)$id, "Edit lantai: {$nama}");
        Session::flash('success', 'Lantai berhasil diperbarui.');
        $this->redirect('lantai');
    }

    public function delete(): void
    {
        $this->verifyCsrf();
        AuthMiddleware::adminOnly();
        $id = (int)$this->post('id');
        $this->model->delete($id);
        $this->logActivity('delete', 'lantai', $id);
        Session::flash('success', 'Lantai berhasil dihapus.');
        $this->redirect('lantai');
    }

    /** AJAX: GET /lantai?gedung_id=x&ajax=1 */
    public function byGedungAjax(): void
    {
        AuthMiddleware::check();
        $gedungId = (int)$this->get('gedung_id');
        $this->json($gedungId ? $this->model->byGedung($gedungId) : []);
    }
}
