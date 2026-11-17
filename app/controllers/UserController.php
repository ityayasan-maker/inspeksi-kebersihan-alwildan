<?php
class UserController extends Controller
{
    private UserModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new UserModel();
    }

    public function index(): void
    {
        AuthMiddleware::adminOnly();
        $users     = $this->model->query("SELECT id,name,email,role,phone,is_active,created_at FROM users ORDER BY role,name");
        $pageTitle = 'Manajemen Pengguna';
        $this->layout('master.user.index', compact('users', 'pageTitle'));
    }

    public function create(): void
    {
        AuthMiddleware::adminOnly();
        $pageTitle = 'Tambah Pengguna';
        $this->layout('master.user.form', compact('pageTitle'));
    }

    public function store(): void
    {
        AuthMiddleware::adminOnly();
        $this->verifyCsrf();
        [$data, $err] = $this->validateUser();
        if ($err) { Session::flash('error', $err); $this->redirect('users/create'); }
        if ($this->model->emailExists($data['email'])) { Session::flash('error', 'Email sudah terdaftar.'); $this->redirect('users/create'); }
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        unset($data['password_confirm']);
        $id = $this->model->insert($data);
        $this->logActivity('create', 'users', $id, "Tambah user: {$data['name']}");
        Session::flash('success', 'Pengguna berhasil ditambahkan.');
        $this->redirect('users');
    }

    public function edit(string $id): void
    {
        AuthMiddleware::adminOnly();
        $user = $this->model->find((int)$id);
        if (!$user) { Session::flash('error', 'Pengguna tidak ditemukan.'); $this->redirect('users'); }
        $pageTitle = 'Edit Pengguna';
        $this->layout('master.user.form', compact('user', 'pageTitle'));
    }

    public function update(string $id): void
    {
        AuthMiddleware::adminOnly();
        $this->verifyCsrf();
        [$data, $err] = $this->validateUser(true);
        if ($err) { Session::flash('error', $err); $this->redirect("users/edit/{$id}"); }
        if ($this->model->emailExists($data['email'], (int)$id)) { Session::flash('error', 'Email sudah digunakan.'); $this->redirect("users/edit/{$id}"); }
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        } else {
            unset($data['password']);
        }
        unset($data['password_confirm']);
        $this->model->update((int)$id, $data);
        $this->logActivity('update', 'users', (int)$id);
        Session::flash('success', 'Pengguna berhasil diperbarui.');
        $this->redirect('users');
    }

    public function delete(): void
    {
        AuthMiddleware::adminOnly();
        $this->verifyCsrf();
        $id = (int)$this->post('id');
        if ($id === Session::get('user_id')) { Session::flash('error', 'Tidak dapat menghapus akun sendiri.'); $this->redirect('users'); }
        $this->model->update($id, ['is_active' => 0]);
        $this->logActivity('delete', 'users', $id);
        Session::flash('success', 'Pengguna berhasil dinonaktifkan.');
        $this->redirect('users');
    }

    public function profile(): void
    {
        AuthMiddleware::check();
        $user      = $this->model->find(Session::get('user_id'));
        $pageTitle = 'Profil Saya';
        $this->layout('master.user.profile', compact('user', 'pageTitle'));
    }

    public function updateProfile(): void
    {
        AuthMiddleware::check();
        $this->verifyCsrf();
        $id   = Session::get('user_id');
        $name = trim($this->post('name', ''));
        $phone = trim($this->post('phone', ''));
        if (!$name) { Session::flash('error', 'Nama wajib diisi.'); $this->redirect('users/profile'); }

        $update = ['name' => $name, 'phone' => $phone];

        // Ganti password
        $newPw = $this->post('new_password', '');
        if ($newPw) {
            $user = $this->model->find($id);
            if (!password_verify($this->post('current_password',''), $user['password'])) {
                Session::flash('error', 'Password lama salah.'); $this->redirect('users/profile');
            }
            if (strlen($newPw) < 8) { Session::flash('error', 'Password baru minimal 8 karakter.'); $this->redirect('users/profile'); }
            $update['password'] = password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $this->model->update($id, $update);
        Session::set('user_name', $name);
        Session::flash('success', 'Profil berhasil diperbarui.');
        $this->redirect('users/profile');
    }

    private function validateUser(bool $isEdit = false): array
    {
        $name  = trim($this->post('name', ''));
        $email = trim($this->post('email', ''));
        $role  = $this->post('role', 'petugas');
        $phone = trim($this->post('phone', ''));
        $pw    = $this->post('password', '');
        $pw2   = $this->post('password_confirm', '');
        $roles = ['admin','supervisor','petugas'];

        if (!$name || !$email || !in_array($role, $roles)) return [null, 'Data tidak lengkap.'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return [null, 'Format email tidak valid.'];
        if (!$isEdit && !$pw) return [null, 'Password wajib diisi.'];
        if ($pw && strlen($pw) < 8) return [null, 'Password minimal 8 karakter.'];
        if ($pw && $pw !== $pw2) return [null, 'Konfirmasi password tidak cocok.'];

        return [
            compact('name','email','role','phone') + ['password' => $pw, 'password_confirm' => $pw2, 'is_active' => 1],
            null
        ];
    }
}
