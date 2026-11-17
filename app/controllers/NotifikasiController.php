<?php
class NotifikasiController extends Controller
{
    private NotifikasiModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new NotifikasiModel();
        AuthMiddleware::check();
    }

    public function index(): void
    {
        $userId = Session::get('user_id');
        $notifs = $this->model->forUser($userId, 50);
        $this->model->markAllRead($userId);
        $pageTitle = 'Notifikasi';
        $this->layout('notifikasi.index', compact('notifs', 'pageTitle'));
    }

    public function unreadCount(): void
    {
        $count = $this->model->unreadCount(Session::get('user_id'));
        $this->json(['count' => $count]);
    }

    public function markRead(): void
    {
        $this->verifyCsrf();
        $id = (int)$this->post('id');
        if ($id) $this->model->markRead($id, Session::get('user_id'));
        $this->jsonSuccess();
    }
}
