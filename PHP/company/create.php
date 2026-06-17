<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = '会社を追加';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Company</p>
        <h1>会社を追加</h1>
    </div>
    <a class="button button-secondary" href="<?= e(url_for('/index.php')) ?>">戻る</a>
</div>

<section class="form-panel">
    <form class="form-grid" action="<?= e(url_for('/PHP/company/store.php')) ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <?php require __DIR__ . '/form_fields.php'; ?>
        <div class="form-actions">
            <button class="button button-primary" type="submit">保存する</button>
            <a class="button button-ghost" href="<?= e(url_for('/index.php')) ?>">キャンセル</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
