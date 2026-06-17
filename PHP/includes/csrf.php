<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = (string)($_POST['csrf_token'] ?? '');

    // POST 操作は全て同一サイトからの送信だけを受け付ける。
    if ($token === '' || empty($_SESSION['_csrf_token']) || !hash_equals((string)$_SESSION['_csrf_token'], $token)) {
        http_response_code(419);
        exit('セッションの確認に失敗しました。画面を更新して再度お試しください。');
    }
}
