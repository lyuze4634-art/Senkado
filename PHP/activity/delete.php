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

$stmt = db()->prepare('DELETE FROM activities WHERE id = :id AND company_id = :company_id AND user_id = :user_id');
$stmt->execute(['id' => $id, 'company_id' => $companyId, 'user_id' => $user['id']]);

set_flash('success', '予定を削除しました。');
redirect('/PHP/company/detail.php?id=' . $companyId);
