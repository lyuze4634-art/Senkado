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
$activityType = valid_choice((string)($_POST['activity_type'] ?? ''), activity_type_options(), 'その他');
$title = trim((string)($_POST['title'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$dueAt = mysql_datetime_from_local((string)($_POST['due_at'] ?? ''));
$activityStatus = valid_choice((string)($_POST['activity_status'] ?? ''), activity_status_options(), '未着手');
$priority = valid_choice((string)($_POST['priority'] ?? ''), priority_options(), '普通');

if ($title === '' || $dueAt === null) {
    set_flash('error', '予定名と期限を入力してください。');
    redirect('/PHP/activity/edit.php?id=' . $id);
}

$stmt = db()->prepare(
    'UPDATE activities
     SET activity_type = :activity_type,
         title = :title,
         description = :description,
         due_at = :due_at,
         activity_status = :activity_status,
         priority = :priority,
         updated_at = :updated_at
     WHERE id = :id AND company_id = :company_id AND user_id = :user_id'
);
$stmt->execute([
    'activity_type' => $activityType,
    'title' => $title,
    'description' => $description !== '' ? $description : null,
    'due_at' => $dueAt,
    'activity_status' => $activityStatus,
    'priority' => $priority,
    'updated_at' => date('Y-m-d H:i:s'),
    'id' => $id,
    'company_id' => $companyId,
    'user_id' => $user['id'],
]);

$check = db()->prepare('SELECT id FROM activities WHERE id = :id AND user_id = :user_id');
$check->execute(['id' => $id, 'user_id' => $user['id']]);
if (!$check->fetch()) {
    http_response_code(404);
    exit('予定が見つかりません。');
}

set_flash('success', '予定を更新しました。');
redirect('/PHP/company/detail.php?id=' . $companyId);
