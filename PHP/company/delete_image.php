<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

require_post();
verify_csrf();

$user = current_user();
$imageId = (int)($_POST['image_id'] ?? 0);
$companyId = (int)($_POST['company_id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM company_images WHERE id = :id AND company_id = :company_id AND user_id = :user_id');
$stmt->execute(['id' => $imageId, 'company_id' => $companyId, 'user_id' => $user['id']]);
$image = $stmt->fetch();

if ($image) {
    $delete = db()->prepare('DELETE FROM company_images WHERE id = :id AND user_id = :user_id');
    $delete->execute(['id' => $imageId, 'user_id' => $user['id']]);

    $path = UPLOAD_DIR . DIRECTORY_SEPARATOR . basename((string)$image['stored_name']);
    if (is_file($path)) {
        unlink($path);
    }

    set_flash('success', '画像を削除しました。');
}

redirect('/PHP/company/detail.php?id=' . $companyId);
