<?php
/**
 * UploadController
 * Semua endpoint upload gambar terpusat di sini
 * Routes (ditambah di Router):
 *   POST /upload/inspeksi-foto    → foto per item checklist
 *   POST /upload/profile-photo    → foto profil user
 *   POST /upload/delete           → hapus file
 *   GET  /upload/gallery/:id      → gallery inspeksi (AJAX)
 */
class UploadController extends Controller
{
    private Uploader            $uploader;
    private InspeksiDetailModel $detailModel;

    public function __construct()
    {
        parent::__construct();
        $this->uploader    = new Uploader();
        $this->detailModel = new InspeksiDetailModel();
        AuthMiddleware::check();
    }

    /* ── POST /upload/inspeksi-foto ──────────────────────── */
    public function inspeksiFoto(): void
    {
        $this->verifyCsrf();
        $inspeksiId = (int)$this->post('inspeksi_id');
        $standarId  = (int)$this->post('standar_id');

        if (!$inspeksiId || !$standarId) {
            $this->jsonError('Parameter tidak lengkap.');
        }

        // Validasi kepemilikan inspeksi
        $inspeksi = (new InspeksiModel())->find($inspeksiId);
        if (!$inspeksi) {
            $this->jsonError('Inspeksi tidak ditemukan.', 404);
        }
        if (Session::role() === 'petugas' && $inspeksi['inspected_by_id'] != Session::get('user_id')) {
            $this->jsonError('Akses ditolak.', 403);
        }

        if (empty($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->jsonError('Tidak ada file yang diupload.');
        }

        $result = $this->uploader->uploadInspeksiFoto($_FILES['foto'], $inspeksiId);

        if (!$result['success']) {
            $this->jsonError($result['error']);
        }

        // Update kolom foto di inspeksi_detail
        $detail = $this->detailModel->queryOne(
            "SELECT id, foto FROM inspeksi_detail WHERE inspeksi_id=? AND standar_id=?",
            [$inspeksiId, $standarId]
        );

        if ($detail) {
            // Hapus foto lama jika ada
            if ($detail['foto']) {
                $this->uploader->delete("inspeksi/{$inspeksiId}/{$detail['foto']}");
            }
            $this->detailModel->update($detail['id'], ['foto' => $result['filename']]);
        }

        $this->logActivity('upload', 'inspeksi_foto', $inspeksiId,
            "foto: {$result['filename']}, standar_id: {$standarId}");

        $this->jsonSuccess([
            'filename'  => $result['filename'],
            'url'       => Helper::uploadUrl("inspeksi/{$inspeksiId}/{$result['filename']}"),
        ], 'Foto berhasil diupload.');
    }

    /* ── POST /upload/profile-photo ──────────────────────── */
    public function profilePhoto(): void
    {
        $this->verifyCsrf();
        if (empty($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->jsonError('Tidak ada file yang diupload.');
        }

        $userId = Session::get('user_id');
        $result = $this->uploader->uploadProfilePhoto($_FILES['photo'], $userId);

        if (!$result['success']) {
            $this->jsonError($result['error']);
        }

        // Update DB & session
        $userModel = new UserModel();
        $oldUser   = $userModel->find($userId);
        if ($oldUser['photo']) {
            $this->uploader->delete("profile/{$oldUser['photo']}");
        }
        $userModel->update($userId, ['photo' => $result['filename']]);
        Session::set('user_photo', $result['filename']);

        $this->jsonSuccess([
            'filename' => $result['filename'],
            'url'      => Helper::uploadUrl("profile/{$result['filename']}"),
        ], 'Foto profil berhasil diperbarui.');
    }

    /* ── POST /upload/delete ─────────────────────────────── */
    public function deleteFile(): void
    {
        $this->verifyCsrf();
        AuthMiddleware::supervisorUp();

        $inspeksiId = (int)$this->post('inspeksi_id');
        $standarId  = (int)$this->post('standar_id');
        $filename   = basename($this->post('filename', ''));

        if (!$inspeksiId || !$standarId || !$filename) {
            $this->jsonError('Parameter tidak lengkap.');
        }

        $detail = $this->detailModel->queryOne(
            "SELECT id FROM inspeksi_detail WHERE inspeksi_id=? AND standar_id=?",
            [$inspeksiId, $standarId]
        );

        if ($detail) {
            $this->detailModel->update($detail['id'], ['foto' => null]);
        }

        $this->uploader->delete("inspeksi/{$inspeksiId}/{$filename}");
        $this->logActivity('delete_foto', 'inspeksi_foto', $inspeksiId, $filename);
        $this->jsonSuccess(null, 'Foto berhasil dihapus.');
    }

    /* ── GET /upload/gallery/:id (AJAX) ──────────────────── */
    public function gallery(string $inspeksiId): void
    {
        $iid = (int)$inspeksiId;
        $photos = [];

        // 1. Ambil foto dokumentasi ruangan utama dari tabel inspeksi
        $inspeksiModel = new InspeksiModel();
        $inspeksi = $inspeksiModel->find($iid);

        if ($inspeksi && !empty($inspeksi['foto_ruangan'])) {
            $photos[] = [
                'id'         => 0,
                'standar_id' => 0,
                'aspek'      => 'Dokumentasi Kondisi Ruangan',
                'kondisi'    => $inspeksi['status'] ?? 'bersih',
                'catatan'    => $inspeksi['catatan_umum'] ?? 'Kondisi umum ruangan saat inspeksi dimulai',
                'filename'   => $inspeksi['foto_ruangan'],
                'url'        => Helper::uploadUrl("inspeksi/{$iid}/{$inspeksi['foto_ruangan']}"),
                'is_utama'   => true,
            ];
        }

        // 2. Ambil foto-foto checklist detail
        $details = $this->detailModel->query(
            "SELECT d.id, d.standar_id, d.foto, d.kondisi, d.catatan, COALESCE(sk.aspek, 'Item Checklist') AS aspek
             FROM inspeksi_detail d
             LEFT JOIN standar_kebersihan sk ON sk.id = d.standar_id
             WHERE d.inspeksi_id = ? AND d.foto IS NOT NULL AND d.foto != ''
             ORDER BY d.id ASC",
            [$iid]
        );

        foreach ($details as $d) {
            $photos[] = [
                'id'         => $d['id'],
                'standar_id' => $d['standar_id'],
                'aspek'      => $d['aspek'],
                'kondisi'    => $d['kondisi'],
                'catatan'    => $d['catatan'] ?? '',
                'filename'   => $d['foto'],
                'url'        => Helper::uploadUrl("inspeksi/{$iid}/{$d['foto']}"),
                'is_utama'   => false,
            ];
        }

        $this->json([
            'total'        => count($photos),
            'ruangan_nama' => $inspeksi['ruangan_nama'] ?? '',
            'tanggal'      => $inspeksi['tanggal'] ?? '',
            'photos'       => $photos
        ]);
    }
}
