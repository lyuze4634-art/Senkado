<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

require_post();
verify_csrf();

$name = trim((string)($_POST['name'] ?? ''));
$email = strtolower(trim((string)($_POST['email'] ?? '')));
$password = (string)($_POST['password'] ?? '');
$passwordConfirm = (string)($_POST['password_confirm'] ?? '');

if ($name === '' || $email === '' || $password === '' || $passwordConfirm === '') {
    set_flash('error', '必要項目を入力してください。');
    redirect('/PHP/auth/register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'メールアドレスの形式を確認してください。');
    redirect('/PHP/auth/register.php');
}

if (mb_strlen($name) > 50 || mb_strlen($email) > 191) {
    set_flash('error', '入力文字数を確認してください。');
    redirect('/PHP/auth/register.php');
}

if (strlen($password) < 8) {
    set_flash('error', 'パスワードは8文字以上で入力してください。');
    redirect('/PHP/auth/register.php');
}

if ($password !== $passwordConfirm) {
    set_flash('error', '確認用パスワードが一致しません。');
    redirect('/PHP/auth/register.php');
}

$pdo = db();
$exists = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$exists->execute(['email' => $email]);

if ($exists->fetch()) {
    set_flash('error', 'このメールアドレスはすでに登録されています。');
    redirect('/PHP/auth/register.php');
}

$now = date('Y-m-d H:i:s');
$stmt = $pdo->prepare(
    'INSERT INTO users (name, email, password_hash, created_at, updated_at)
     VALUES (:name, :email, :password_hash, :created_at, :updated_at)'
);
$stmt->execute([
    'name' => $name,
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'created_at' => $now,
    'updated_at' => $now,
]);

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$pdo->lastInsertId();
$_SESSION['user_name'] = $name;
$_SESSION['user_email'] = $email;

set_flash('success', '登録が完了しました。');
redirect('/index.php');
