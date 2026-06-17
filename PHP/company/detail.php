<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$user = current_user();
$id = (int)($_GET['id'] ?? 0);
$pdo = db();

$companyStmt = $pdo->prepare('SELECT * FROM companies WHERE id = :id AND user_id = :user_id');
$companyStmt->execute(['id' => $id, 'user_id' => $user['id']]);
$company = $companyStmt->fetch();

if (!$company) {
    http_response_code(404);
    exit('会社が見つかりません。');
}

$activityStmt = $pdo->prepare(
    'SELECT *
     FROM activities
     WHERE company_id = :company_id AND user_id = :user_id
     ORDER BY due_at ASC, id ASC'
);
$activityStmt->execute(['company_id' => $id, 'user_id' => $user['id']]);
$activities = $activityStmt->fetchAll();

$imageStmt = $pdo->prepare(
    'SELECT *
     FROM company_images
     WHERE company_id = :company_id AND user_id = :user_id
     ORDER BY sort_order ASC, id ASC'
);
$imageStmt->execute(['company_id' => $id, 'user_id' => $user['id']]);
$images = $imageStmt->fetchAll();

$pageTitle = $company['company_name'];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Company Detail</p>
        <h1><?= e($company['company_name']) ?></h1>
    </div>
    <div class="action-row">
        <form class="inline-form" action="<?= e(url_for('/PHP/company/toggle_favorite.php')) ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($company['id']) ?>">
            <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? url_for('/PHP/company/detail.php?id=' . $company['id'])) ?>">
            <button class="button button-secondary" type="submit">
                <?= (int)($company['is_favorite'] ?? 0) === 1 ? '★ お気に入り解除' : '☆ お気に入り' ?>
            </button>
        </form>
        <a class="button button-secondary" href="<?= e(url_for('/PHP/activity/create.php?company_id=' . $company['id'])) ?>">予定を追加</a>
        <a class="button button-secondary" href="<?= e(url_for('/PHP/company/edit.php?id=' . $company['id'])) ?>">編集</a>
        <a class="button button-ghost" href="<?= e(url_for('/index.php')) ?>">一覧へ</a>
    </div>
</div>

<section class="detail-layout">
    <div class="detail-main">
        <section class="detail-section">
            <div class="section-heading">
                <h2>基本情報</h2>
            </div>
            <dl class="definition-list">
                <div>
                    <dt>選考状況</dt>
                    <dd><span class="status-pill"><?= e($company['company_status']) ?></span></dd>
                </div>
                <div>
                    <dt>業界・分類</dt>
                    <dd><?= e($company['industry'] ?: '-') ?></dd>
                </div>
                <div>
                    <dt>公式サイト</dt>
                    <dd>
                        <?php if ($company['official_url']): ?>
                            <a href="<?= e($company['official_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($company['official_url']) ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt>メモ</dt>
                    <dd class="pre-line"><?= render_text_with_links($company['note'] ?? '') ?: '-' ?></dd>
                </div>
            </dl>
        </section>

        <section class="detail-section">
            <div class="section-heading">
                <h2>選考予定</h2>
                <a class="button button-small" href="<?= e(url_for('/PHP/activity/create.php?company_id=' . $company['id'])) ?>">追加</a>
            </div>

            <?php if (!$activities): ?>
                <p class="empty-state">登録された予定はありません。</p>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($activities as $activity): ?>
                        <?php $state = deadline_state($activity['due_at']); ?>
                        <article class="timeline-item deadline-<?= e($state) ?>">
                            <div class="timeline-meta">
                                <span><?= e($activity['activity_type']) ?></span>
                                <strong><?= e(format_datetime_jp($activity['due_at'])) ?></strong>
                            </div>
                            <div class="timeline-body">
                                <div class="timeline-title-row">
                                    <h3><?= e($activity['title']) ?></h3>
                                    <span class="status-pill"><?= e($activity['activity_status']) ?></span>
                                </div>
                                <p class="deadline-text"><?= e(deadline_label($activity['due_at'])) ?> / 優先度 <?= e($activity['priority']) ?></p>
                                <?php if (!empty($activity['description'])): ?>
                                    <p class="pre-line"><?= render_text_with_links($activity['description']) ?></p>
                                <?php endif; ?>
                                <div class="compact-actions">
                                    <?php if (!in_array($activity['activity_status'], ['完了', 'キャンセル'], true)): ?>
                                        <form action="<?= e(url_for('/PHP/activity/update_status.php')) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= e($activity['id']) ?>">
                                            <input type="hidden" name="company_id" value="<?= e($company['id']) ?>">
                                            <input type="hidden" name="activity_status" value="完了">
                                            <button class="button button-small" type="submit">完了にする</button>
                                        </form>
                                    <?php endif; ?>
                                    <a class="button button-small button-secondary" href="<?= e(url_for('/PHP/activity/edit.php?id=' . $activity['id'])) ?>">編集</a>
                                    <form action="<?= e(url_for('/PHP/activity/delete.php')) ?>" method="post" data-confirm="この予定を削除しますか。">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= e($activity['id']) ?>">
                                        <input type="hidden" name="company_id" value="<?= e($company['id']) ?>">
                                        <button class="button button-small button-danger" type="submit">削除</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <aside class="detail-side">
        <section class="detail-section">
            <div class="section-heading">
                <h2>画像</h2>
            </div>

            <?php if ($images): ?>
                <?php $firstImageUrl = url_for('/uploads/company_images/' . $images[0]['stored_name']); ?>
                <button class="image-preview" type="button" data-lightbox-trigger data-image-src="<?= e($firstImageUrl) ?>" data-image-alt="<?= e($images[0]['original_name']) ?>">
                    <img src="<?= e($firstImageUrl) ?>" alt="<?= e($images[0]['original_name']) ?>" data-gallery-main>
                </button>
                <div class="thumbnail-grid">
                    <?php foreach ($images as $image): ?>
                        <?php $imageUrl = url_for('/uploads/company_images/' . $image['stored_name']); ?>
                        <div class="thumbnail-item">
                            <button type="button" class="thumbnail-button" data-gallery-thumb data-image-src="<?= e($imageUrl) ?>" data-image-alt="<?= e($image['original_name']) ?>">
                                <img src="<?= e($imageUrl) ?>" alt="<?= e($image['original_name']) ?>">
                            </button>
                            <form action="<?= e(url_for('/PHP/company/delete_image.php')) ?>" method="post" data-confirm="この画像を削除しますか。">
                                <?= csrf_field() ?>
                                <input type="hidden" name="image_id" value="<?= e($image['id']) ?>">
                                <input type="hidden" name="company_id" value="<?= e($company['id']) ?>">
                                <button class="text-button" type="submit">削除</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-state">画像はまだありません。</p>
            <?php endif; ?>

            <form class="upload-form" action="<?= e(url_for('/PHP/company/upload_image.php')) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="company_id" value="<?= e($company['id']) ?>">
                <label>
                    <span>画像を追加</span>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                </label>
                <button class="button button-secondary full-width" type="submit">アップロード</button>
            </form>
        </section>

        <section class="detail-section danger-zone">
            <h2>削除</h2>
            <form action="<?= e(url_for('/PHP/company/delete.php')) ?>" method="post" data-confirm="会社と関連する予定をすべて削除しますか。">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= e($company['id']) ?>">
                <button class="button button-danger full-width" type="submit">会社を削除</button>
            </form>
        </section>
    </aside>
</section>

<div class="lightbox" data-lightbox hidden>
    <button class="lightbox-close" type="button" data-lightbox-close aria-label="閉じる">×</button>
    <img src="" alt="" data-lightbox-image>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
