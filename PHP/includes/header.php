<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

$pageTitle = $pageTitle ?? APP_NAME;
$bodyClass = $bodyClass ?? '';
$user = current_user();
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url_for('/CSS/base.css')) ?>">
    <link rel="stylesheet" href="<?= e(url_for('/CSS/dashboard.css')) ?>">
    <link rel="stylesheet" href="<?= e(url_for('/CSS/form.css')) ?>">
    <script src="<?= e(url_for('/JS/main.js')) ?>" defer></script>
    <script src="<?= e(url_for('/JS/deadline.js')) ?>" defer></script>
</head>
<body class="<?= e($bodyClass) ?>">
<header class="site-header">
    <a class="brand" href="<?= e(url_for('/index.php')) ?>"><?= e(APP_NAME) ?></a>
    <?php if ($user): ?>
        <nav class="top-nav" aria-label="Global">
            <span class="current-user"><?= e($user['name']) ?></span>
            <a class="button button-secondary" href="<?= e(url_for('/PHP/company/create.php')) ?>">会社を追加</a>
            <a class="button button-ghost" href="<?= e(url_for('/PHP/export/csv.php')) ?>">CSV出力</a>
            <a class="button button-ghost" href="<?= e(url_for('/PHP/auth/change_password.php')) ?>">パスワード変更</a>
            <form class="inline-form" action="<?= e(url_for('/PHP/auth/logout.php')) ?>" method="post">
                <?= csrf_field() ?>
                <button class="button button-ghost" type="submit">ログアウト</button>
            </form>
        </nav>
    <?php endif; ?>
</header>

<main class="page-shell">
    <?php foreach (pull_flash() as $flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>" role="status">
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>
