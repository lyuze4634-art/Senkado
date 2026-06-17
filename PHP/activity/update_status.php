<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

require_post();
verify_csrf();

$user = current_user();
$id = (int)($_POST['id'] ?? 0);
$companyId = (int)($_POST['company_id'] ?? 0);
$status = valid_choice((string)($_POST['activity_status'] ?? ''), activity_status_options(), '完了');

$stmt = db()->prepare(
    'UPDATE activities
     SET activity_status = :activity_status, updated_at = :updated_at
     WHERE id = :id AND company_id = :company_id AND user_id = :user_id'
);
$stmt->execute([
    'activity_status' => $status,
    'updated_at' => date('Y-m-d H:i:s'),
    'id' => $id,
    'company_id' => $companyId,
    'user_id' => $user['id'],
]);

redirect('/PHP/company/detail.php?id=' . $companyId);
