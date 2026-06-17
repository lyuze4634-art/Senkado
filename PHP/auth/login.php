<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('/index.php');
}

$pageTitle = 'ログイン';
$bodyClass = 'auth-page';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="auth-panel">
    <div class="auth-heading">
        <p class="eyebrow">Login</p>
        <h1>ログイン</h1>
    </div>

    <form class="form-grid" action="<?= e(url_for('/PHP/auth/login_process.php')) ?>" method="post" novalidate>
        <?= csrf_field() ?>

        <label>
            <span>メールアドレス</span>
            <input type="email" name="email" maxlength="191" required autocomplete="email">
        </label>

        <label>
            <span>パスワード</span>
            <input type="password" name="password" required autocomplete="current-password">
        </label>

        <button class="button button-primary full-width" type="submit">ログイン</button>
    </form>

    <p class="auth-switch">
        初めて利用しますか。
        <a href="<?= e(url_for('/PHP/auth/register.php')) ?>">アカウント登録</a>
    </p>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
