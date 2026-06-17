<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$user = current_user();
$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare(
    'SELECT a.*, c.company_name
     FROM activities a
     INNER JOIN companies c ON c.id = a.company_id AND c.user_id = a.user_id
     WHERE a.id = :id AND a.user_id = :user_id'
);
$stmt->execute(['id' => $id, 'user_id' => $user['id']]);
$activity = $stmt->fetch();

if (!$activity) {
    http_response_code(404);
    exit('予定が見つかりません。');
}

$pageTitle = '予定を編集';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow"><?= e($activity['company_name']) ?></p>
        <h1>予定を編集</h1>
    </div>
    <a class="button button-secondary" href="<?= e(url_for('/PHP/company/detail.php?id=' . $activity['company_id'])) ?>">戻る</a>
</div>

<section class="form-panel">
    <form class="form-grid" action="<?= e(url_for('/PHP/activity/update.php')) ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e($activity['id']) ?>">
        <?php require __DIR__ . '/form_fields.php'; ?>
        <div class="form-actions">
            <button class="button button-primary" type="submit">更新する</button>
            <a class="button button-ghost" href="<?= e(url_for('/PHP/company/detail.php?id=' . $activity['company_id'])) ?>">キャンセル</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
