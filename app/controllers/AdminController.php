<?php
/**
 * AdminController — fitur khusus admin
 * Routes: GET /admin/activity-log
 */
class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        AuthMiddleware::adminOnly();
    }

    public function activityLog(): void
    {
        $logModel    = new ActivityLogModel();
        $filterModule= trim($this->get('module', ''));
        $filterUser  = (int)$this->get('user_id', 0);

        if ($filterModule && $filterUser) {
            $logs = $logModel->query(
                "SELECT al.*, u.name AS user_name, u.role AS user_role
                 FROM activity_logs al LEFT JOIN users u ON u.id=al.user_id
                 WHERE al.module=? AND al.user_id=?
                 ORDER BY al.created_at DESC LIMIT 500",
                [$filterModule, $filterUser]
            );
        } elseif ($filterModule) {
            $logs = $logModel->byModule($filterModule, 500);
        } elseif ($filterUser) {
            $logs = $logModel->recent(500, $filterUser);
        } else {
            $logs = $logModel->recent(200);
        }

        $users     = (new UserModel())->query(
            "SELECT id, name FROM users ORDER BY name"
        );
        $pageTitle = 'Activity Log';
        $this->layout('admin.activity_log', compact(
            'logs', 'users', 'filterModule', 'filterUser', 'pageTitle'
        ));
    }
}
