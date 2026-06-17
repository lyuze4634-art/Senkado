<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

require_post();
verify_csrf();

$user = current_user();
$id = (int)($_POST['id'] ?? 0);
$redirectTo = (string)($_POST['redirect_to'] ?? '/index.php');

$stmt = db()->prepare(
    'UPDATE companies
     SET is_favorite = CASE WHEN is_favorite = 1 THEN 0 ELSE 1 END,
         updated_at = :updated_at
     WHERE id = :id AND user_id = :user_id'
);
$stmt->execute([
    'updated_at' => date('Y-m-d H:i:s'),
    'id' => $id,
    'user_id' => $user['id'],
]);

// リダイレクト先は同一サイト内の相対パスだけを許可する。
if ($redirectTo === '' || $redirectTo[0] !== '/' || str_starts_with($redirectTo, '//') || preg_match("/[\r\n]/", $redirectTo)) {
    $redirectTo = url_for('/index.php');
}

header('Location: ' . $redirectTo);
exit;
