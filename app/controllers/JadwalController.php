<?php
/**
 * JadwalController
 * Controller untuk Manajemen Jadwal Foto Kebersihan Karyawan Al-Wildan 8
 */
class JadwalController extends Controller
{
    private JadwalModel $model;

    public function __construct()
    {
        parent::__construct();
        AuthMiddleware::check();
        $this->model = new JadwalModel();
    }

    /**
     * Tampilan Jadwal Kebersihan Harian & Status Foto Realtime
     */
    public function index(): void
    {
        $role       = Session::role();
        $userId     = (int)Session::get('user_id');
        $date       = $this->get('date', date('Y-m-d'));
        $sesi       = $this->get('sesi', '');
        $petugasId  = (int)$this->get('petugas_id', ($role === 'petugas' ? $userId : 0));
        $gedungId   = (int)$this->get('gedung_id', 0);

        // Ambil data status hari ini
        $jadwalList = $this->model->todayStatus($date, $petugasId, $sesi);
        $summary    = $this->model->summaryHariIni($date);

        // Master data untuk dropdown filter & modal tambah jadwal
        $userModel  = new UserModel();
        $petugasList= $userModel->allActive('petugas');

        $ruanganModel = new RuanganModel();
        $ruanganList  = $ruanganModel->allWithRelasi();

        $gedungModel  = new GedungModel();
        $gedungList   = $gedungModel->allActive();

        $pageTitle  = 'Jadwal Foto Kebersihan';

        $this->layout('jadwal.index', compact(
            'jadwalList', 'summary', 'date', 'sesi', 'petugasId', 'gedungId',
            'petugasList', 'ruanganList', 'gedungList', 'pageTitle', 'role', 'userId'
        ));
    }

    /**
     * Tambah Penugasan Jadwal Baru (Admin / Supervisor)
     */
    public function store(): void
    {
        if (!Session::isRole(['admin', 'supervisor'])) {
            $this->redirect('jadwal', ['error' => 'Akses ditolak.']);
            return;
        }

        $petugasId = (int)$this->post('petugas_id');
        $ruanganId = (int)$this->post('ruangan_id');
        $sesi      = $this->post('sesi', 'pagi');
        $jamMulai  = $this->post('jam_mulai', ($sesi === 'pagi' ? '06:30' : '11:30'));
        $jamBatas  = $this->post('jam_batas', ($sesi === 'pagi' ? '09:00' : '14:00'));
        $catatan   = trim($this->post('catatan', ''));

        if (!$petugasId || !$ruanganId) {
            Session::flash('error', 'Petugas dan ruangan wajib dipilih.');
            $this->redirect('jadwal');
            return;
        }

        $this->model->insert([
            'petugas_id' => $petugasId,
            'ruangan_id' => $ruanganId,
            'sesi'       => $sesi,
            'hari'       => 'setiap_hari',
            'jam_mulai'  => $jamMulai,
            'jam_batas'  => $jamBatas,
            'catatan'    => $catatan,
            'is_active'  => 1
        ]);

        Session::flash('success', 'Jadwal baru berhasil ditambahkan.');
        $this->redirect('jadwal');
    }

    /**
     * Hapus Penugasan Jadwal (Admin / Supervisor)
     */
    public function delete(): void
    {
        if (!Session::isRole(['admin', 'supervisor'])) {
            $this->redirect('jadwal', ['error' => 'Akses ditolak.']);
            return;
        }

        $id = (int)$this->post('id');
        if ($id) {
            $this->model->delete($id);
            Session::flash('success', 'Jadwal berhasil dihapus.');
        }

        $this->redirect('jadwal');
    }
}
