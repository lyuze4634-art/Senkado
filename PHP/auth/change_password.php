<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'パスワード変更';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Account</p>
        <h1>パスワード変更</h1>
    </div>
    <a class="button button-secondary" href="<?= e(url_for('/index.php')) ?>">戻る</a>
</div>

<section class="form-panel">
    <form class="form-grid" action="<?= e(url_for('/PHP/auth/change_password_process.php')) ?>" method="post" novalidate>
        <?= csrf_field() ?>

        <label class="span-2">
            <span>現在のパスワード</span>
            <input type="password" name="current_password" required autocomplete="current-password">
        </label>

        <label>
            <span>新しいパスワード</span>
            <input type="password" name="new_password" minlength="8" required autocomplete="new-password">
        </label>

        <label>
            <span>新しいパスワード確認</span>
            <input type="password" name="new_password_confirm" minlength="8" required autocomplete="new-password">
        </label>

        <div class="form-actions">
            <button class="button button-primary" type="submit">更新する</button>
            <a class="button button-ghost" href="<?= e(url_for('/index.php')) ?>">キャンセル</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
