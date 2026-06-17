<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

require_post();
verify_csrf();

$user = current_user();
$companyId = (int)($_POST['company_id'] ?? 0);
$activityType = valid_choice((string)($_POST['activity_type'] ?? ''), activity_type_options(), 'その他');
$title = trim((string)($_POST['title'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$dueAt = mysql_datetime_from_local((string)($_POST['due_at'] ?? ''));
$activityStatus = valid_choice((string)($_POST['activity_status'] ?? ''), activity_status_options(), '未着手');
$priority = valid_choice((string)($_POST['priority'] ?? ''), priority_options(), '普通');

$companyStmt = db()->prepare('SELECT id FROM companies WHERE id = :id AND user_id = :user_id');
$companyStmt->execute(['id' => $companyId, 'user_id' => $user['id']]);

if (!$companyStmt->fetch()) {
    http_response_code(404);
    exit('会社が見つかりません。');
}

if ($title === '' || $dueAt === null) {
    set_flash('error', '予定名と期限を入力してください。');
    redirect('/PHP/activity/create.php?company_id=' . $companyId);
}

$now = date('Y-m-d H:i:s');
$stmt = db()->prepare(
    'INSERT INTO activities (user_id, company_id, activity_type, title, description, due_at, activity_status, priority, created_at, updated_at)
     VALUES (:user_id, :company_id, :activity_type, :title, :description, :due_at, :activity_status, :priority, :created_at, :updated_at)'
);
$stmt->execute([
    'user_id' => $user['id'],
    'company_id' => $companyId,
    'activity_type' => $activityType,
    'title' => $title,
    'description' => $description !== '' ? $description : null,
    'due_at' => $dueAt,
    'activity_status' => $activityStatus,
    'priority' => $priority,
    'created_at' => $now,
    'updated_at' => $now,
]);

set_flash('success', '予定を追加しました。');
redirect('/PHP/company/detail.php?id=' . $companyId);
