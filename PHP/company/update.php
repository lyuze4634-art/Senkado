<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

require_post();
verify_csrf();

$user = current_user();
$id = (int)($_POST['id'] ?? 0);
$companyName = trim((string)($_POST['company_name'] ?? ''));
$industry = trim((string)($_POST['industry'] ?? ''));
$officialUrlRaw = trim((string)($_POST['official_url'] ?? ''));
$officialUrl = normalize_optional_url($officialUrlRaw);
$companyStatus = valid_choice((string)($_POST['company_status'] ?? ''), company_status_options(), '準備中');
$note = trim((string)($_POST['note'] ?? ''));

if ($companyName === '') {
    set_flash('error', '会社名を入力してください。');
    redirect('/PHP/company/edit.php?id=' . $id);
}

if ($officialUrlRaw !== '' && $officialUrl === null) {
    set_flash('error', '公式サイトのURL形式を確認してください。');
    redirect('/PHP/company/edit.php?id=' . $id);
}

$stmt = db()->prepare(
    'UPDATE companies
     SET company_name = :company_name,
         industry = :industry,
         official_url = :official_url,
         company_status = :company_status,
         note = :note,
         updated_at = :updated_at
     WHERE id = :id AND user_id = :user_id'
);
$stmt->execute([
    'company_name' => $companyName,
    'industry' => $industry !== '' ? $industry : null,
    'official_url' => $officialUrl,
    'company_status' => $companyStatus,
    'note' => $note !== '' ? $note : null,
    'updated_at' => date('Y-m-d H:i:s'),
    'id' => $id,
    'user_id' => $user['id'],
]);

if ($stmt->rowCount() === 0) {
    $check = db()->prepare('SELECT id FROM companies WHERE id = :id AND user_id = :user_id');
    $check->execute(['id' => $id, 'user_id' => $user['id']]);
    if (!$check->fetch()) {
        http_response_code(404);
        exit('会社が見つかりません。');
    }
}

set_flash('success', '会社情報を更新しました。');
redirect('/PHP/company/detail.php?id=' . $id);
