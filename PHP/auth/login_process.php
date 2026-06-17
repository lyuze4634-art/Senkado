<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

require_post();
verify_csrf();

$email = strtolower(trim((string)($_POST['email'] ?? '')));
$password = (string)($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    set_flash('error', 'メールアドレスとパスワードを入力してください。');
    redirect('/PHP/auth/login.php');
}

$stmt = db()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, (string)$user['password_hash'])) {
    set_flash('error', 'ログイン情報が正しくありません。');
    redirect('/PHP/auth/login.php');
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['user_name'] = (string)$user['name'];
$_SESSION['user_email'] = (string)$user['email'];

redirect('/index.php');
