<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$user = current_user();
$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM companies WHERE id = :id AND user_id = :user_id');
$stmt->execute(['id' => $id, 'user_id' => $user['id']]);
$company = $stmt->fetch();

if (!$company) {
    http_response_code(404);
    exit('会社が見つかりません。');
}

$pageTitle = '会社を編集';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Company</p>
        <h1>会社を編集</h1>
    </div>
    <a class="button button-secondary" href="<?= e(url_for('/PHP/company/detail.php?id=' . $company['id'])) ?>">戻る</a>
</div>

<section class="form-panel">
    <form class="form-grid" action="<?= e(url_for('/PHP/company/update.php')) ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e($company['id']) ?>">
        <?php require __DIR__ . '/form_fields.php'; ?>
        <div class="form-actions">
            <button class="button button-primary" type="submit">更新する</button>
            <a class="button button-ghost" href="<?= e(url_for('/PHP/company/detail.php?id=' . $company['id'])) ?>">キャンセル</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
