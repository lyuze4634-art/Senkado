<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$user = current_user();
$companyId = (int)($_GET['company_id'] ?? 0);

$companyStmt = db()->prepare('SELECT * FROM companies WHERE id = :id AND user_id = :user_id');
$companyStmt->execute(['id' => $companyId, 'user_id' => $user['id']]);
$company = $companyStmt->fetch();

if (!$company) {
    http_response_code(404);
    exit('会社が見つかりません。');
}

$activity = [
    'company_id' => $companyId,
    'activity_type' => 'その他',
    'title' => '',
    'description' => '',
    'due_at' => '',
    'activity_status' => '未着手',
    'priority' => '普通',
];

$pageTitle = '予定を追加';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow"><?= e($company['company_name']) ?></p>
        <h1>予定を追加</h1>
    </div>
    <a class="button button-secondary" href="<?= e(url_for('/PHP/company/detail.php?id=' . $companyId)) ?>">戻る</a>
</div>

<section class="form-panel">
    <form class="form-grid" action="<?= e(url_for('/PHP/activity/store.php')) ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <?php require __DIR__ . '/form_fields.php'; ?>
        <div class="form-actions">
            <button class="button button-primary" type="submit">保存する</button>
            <a class="button button-ghost" href="<?= e(url_for('/PHP/company/detail.php?id=' . $companyId)) ?>">キャンセル</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
