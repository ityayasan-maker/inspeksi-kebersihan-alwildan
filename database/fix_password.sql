-- Query untuk update / reset password superadmin Al Wildan
-- Password default: Admin@123
UPDATE `users`
SET `password` = '$2y$12$/L2K2s3VEHvd527c19iIYuLGURVehTqdjMYrrcUp41j/lYj9nyMBe'
WHERE `email` = 'admin@alwildan.sch.id';
