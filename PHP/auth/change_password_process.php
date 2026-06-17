<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

require_post();
verify_csrf();

$user = current_user();
$currentPassword = (string)($_POST['current_password'] ?? '');
$newPassword = (string)($_POST['new_password'] ?? '');
$newPasswordConfirm = (string)($_POST['new_password_confirm'] ?? '');

if ($currentPassword === '' || $newPassword === '' || $newPasswordConfirm === '') {
    set_flash('error', '必要項目を入力してください。');
    redirect('/PHP/auth/change_password.php');
}

if (strlen($newPassword) < 8) {
    set_flash('error', '新しいパスワードは8文字以上で入力してください。');
    redirect('/PHP/auth/change_password.php');
}

if ($newPassword !== $newPasswordConfirm) {
    set_flash('error', '確認用パスワードが一致しません。');
    redirect('/PHP/auth/change_password.php');
}

$pdo = db();
$stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $user['id']]);
$account = $stmt->fetch();

if (!$account || !password_verify($currentPassword, (string)$account['password_hash'])) {
    set_flash('error', '現在のパスワードが正しくありません。');
    redirect('/PHP/auth/change_password.php');
}

if (password_verify($newPassword, (string)$account['password_hash'])) {
    set_flash('error', '現在とは異なるパスワードを設定してください。');
    redirect('/PHP/auth/change_password.php');
}

// パスワード変更後もログイン状態は維持しつつ、セッションIDは再発行する。
$update = $pdo->prepare(
    'UPDATE users
     SET password_hash = :password_hash, updated_at = :updated_at
     WHERE id = :id'
);
$update->execute([
    'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
    'updated_at' => date('Y-m-d H:i:s'),
    'id' => $user['id'],
]);

session_regenerate_id(true);

set_flash('success', 'パスワードを更新しました。');
redirect('/index.php');
