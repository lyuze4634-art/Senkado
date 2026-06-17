<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

require_post();
verify_csrf();

$user = current_user();
$id = (int)($_POST['id'] ?? 0);
$pdo = db();

$images = $pdo->prepare('SELECT stored_name FROM company_images WHERE company_id = :company_id AND user_id = :user_id');
$images->execute(['company_id' => $id, 'user_id' => $user['id']]);

$stmt = $pdo->prepare('DELETE FROM companies WHERE id = :id AND user_id = :user_id');
$stmt->execute(['id' => $id, 'user_id' => $user['id']]);

if ($stmt->rowCount() > 0) {
    foreach ($images->fetchAll() as $image) {
        $path = UPLOAD_DIR . DIRECTORY_SEPARATOR . basename((string)$image['stored_name']);
        if (is_file($path)) {
            unlink($path);
        }
    }
    set_flash('success', '会社を削除しました。');
}

redirect('/index.php');
