<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('/index.php');
}

$pageTitle = 'アカウント登録';
$bodyClass = 'auth-page';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="auth-panel">
    <div class="auth-heading">
        <p class="eyebrow">Account</p>
        <h1>アカウント登録</h1>
    </div>

    <form class="form-grid" action="<?= e(url_for('/PHP/auth/register_process.php')) ?>" method="post" novalidate>
        <?= csrf_field() ?>

        <label>
            <span>表示名</span>
            <input type="text" name="name" maxlength="50" required autocomplete="name">
        </label>

        <label>
            <span>メールアドレス</span>
            <input type="email" name="email" maxlength="191" required autocomplete="email">
        </label>

        <label>
            <span>パスワード</span>
            <input type="password" name="password" minlength="8" required autocomplete="new-password">
        </label>

        <label>
            <span>パスワード確認</span>
            <input type="password" name="password_confirm" minlength="8" required autocomplete="new-password">
        </label>

        <button class="button button-primary full-width" type="submit">登録する</button>
    </form>

    <p class="auth-switch">
        すでにアカウントをお持ちですか。
        <a href="<?= e(url_for('/PHP/auth/login.php')) ?>">ログイン</a>
    </p>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
