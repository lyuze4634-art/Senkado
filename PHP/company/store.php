<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

require_post();
verify_csrf();

$user = current_user();
$companyName = trim((string)($_POST['company_name'] ?? ''));
$industry = trim((string)($_POST['industry'] ?? ''));
$officialUrlRaw = trim((string)($_POST['official_url'] ?? ''));
$officialUrl = normalize_optional_url($officialUrlRaw);
$companyStatus = valid_choice((string)($_POST['company_status'] ?? ''), company_status_options(), '準備中');
$note = trim((string)($_POST['note'] ?? ''));

if ($companyName === '') {
    set_flash('error', '会社名を入力してください。');
    redirect('/PHP/company/create.php');
}

if ($officialUrlRaw !== '' && $officialUrl === null) {
    set_flash('error', '公式サイトのURL形式を確認してください。');
    redirect('/PHP/company/create.php');
}

$now = date('Y-m-d H:i:s');
$stmt = db()->prepare(
    'INSERT INTO companies (user_id, company_name, industry, official_url, company_status, note, created_at, updated_at)
     VALUES (:user_id, :company_name, :industry, :official_url, :company_status, :note, :created_at, :updated_at)'
);
$stmt->execute([
    'user_id' => $user['id'],
    'company_name' => $companyName,
    'industry' => $industry !== '' ? $industry : null,
    'official_url' => $officialUrl,
    'company_status' => $companyStatus,
    'note' => $note !== '' ? $note : null,
    'created_at' => $now,
    'updated_at' => $now,
]);

set_flash('success', '会社を追加しました。');
redirect('/PHP/company/detail.php?id=' . db()->lastInsertId());
