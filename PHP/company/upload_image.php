<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

require_post();
verify_csrf();

$user = current_user();
$companyId = (int)($_POST['company_id'] ?? 0);
$pdo = db();

$companyStmt = $pdo->prepare('SELECT id FROM companies WHERE id = :id AND user_id = :user_id');
$companyStmt->execute(['id' => $companyId, 'user_id' => $user['id']]);

if (!$companyStmt->fetch()) {
    http_response_code(404);
    exit('会社が見つかりません。');
}

$file = $_FILES['image'] ?? null;

if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
    set_flash('error', '画像を選択してください。');
    redirect('/PHP/company/detail.php?id=' . $companyId);
}

if ((int)$file['size'] > MAX_UPLOAD_BYTES) {
    set_flash('error', '画像は5MB以下にしてください。');
    redirect('/PHP/company/detail.php?id=' . $companyId);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file((string)$file['tmp_name']);
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
];

// 拡張子ではなく実ファイルの MIME を確認し、アップロード偽装を避ける。
if (!isset($allowed[$mimeType])) {
    set_flash('error', '対応形式は JPG、PNG、GIF、WebP です。');
    redirect('/PHP/company/detail.php?id=' . $companyId);
}

ensure_upload_dir();

$storedName = sprintf('%d_%d_%s.%s', $user['id'], $companyId, bin2hex(random_bytes(16)), $allowed[$mimeType]);
$destination = UPLOAD_DIR . DIRECTORY_SEPARATOR . $storedName;

if (!move_uploaded_file((string)$file['tmp_name'], $destination)) {
    set_flash('error', '画像の保存に失敗しました。');
    redirect('/PHP/company/detail.php?id=' . $companyId);
}

$orderStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order FROM company_images WHERE company_id = :company_id AND user_id = :user_id');
$orderStmt->execute(['company_id' => $companyId, 'user_id' => $user['id']]);
$sortOrder = (int)($orderStmt->fetch()['next_order'] ?? 1);

$stmt = $pdo->prepare(
    'INSERT INTO company_images (user_id, company_id, original_name, stored_name, mime_type, file_size, sort_order, created_at)
     VALUES (:user_id, :company_id, :original_name, :stored_name, :mime_type, :file_size, :sort_order, :created_at)'
);
$stmt->execute([
    'user_id' => $user['id'],
    'company_id' => $companyId,
    'original_name' => mb_substr((string)$file['name'], 0, 255),
    'stored_name' => $storedName,
    'mime_type' => $mimeType,
    'file_size' => (int)$file['size'],
    'sort_order' => $sortOrder,
    'created_at' => date('Y-m-d H:i:s'),
]);

set_flash('success', '画像を追加しました。');
redirect('/PHP/company/detail.php?id=' . $companyId);
