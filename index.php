<?php
declare(strict_types=1);

require_once __DIR__ . '/PHP/includes/auth_check.php';
require_once __DIR__ . '/PHP/config/db.php';

$user = current_user();
$pdo = db();
$selectedCompanyId = (int)($_GET['company_id'] ?? 0);
$statusFilter = (string)($_GET['status'] ?? '');
$statusFilter = in_array($statusFilter, company_status_options(), true) ? $statusFilter : '';
$withinSevenDays = isset($_GET['within7']) && $_GET['within7'] === '1';

$companyOptionsStmt = $pdo->prepare(
    'SELECT id, company_name
     FROM companies
     WHERE user_id = :user_id
     ORDER BY company_name ASC'
);
$companyOptionsStmt->execute(['user_id' => $user['id']]);
$companyOptions = $companyOptionsStmt->fetchAll();
$validCompanyIds = array_map(static fn (array $company): int => (int)$company['id'], $companyOptions);

if ($selectedCompanyId > 0 && !in_array($selectedCompanyId, $validCompanyIds, true)) {
    $selectedCompanyId = 0;
}

$sql = 'SELECT * FROM companies WHERE user_id = :user_id';
$params = ['user_id' => $user['id']];

if ($selectedCompanyId > 0) {
    $sql .= ' AND id = :company_id';
    $params['company_id'] = $selectedCompanyId;
}

if ($statusFilter !== '') {
    $sql .= ' AND company_status = :company_status';
    $params['company_status'] = $statusFilter;
}

$sql .= ' ORDER BY company_name ASC';
$companyStmt = $pdo->prepare($sql);
$companyStmt->execute($params);
$companies = $companyStmt->fetchAll();

$nextStmt = $pdo->prepare(
    'SELECT *
     FROM activities
     WHERE user_id = :user_id
       AND company_id = :company_id
       AND activity_status NOT IN (\'完了\', \'キャンセル\')
     ORDER BY due_at ASC, id ASC
     LIMIT 1'
);

$cards = [];
$stats = [
    'total' => 0,
    'expired' => 0,
    'urgent' => 0,
    'empty' => 0,
];
$sevenDayLimit = (new DateTimeImmutable('+7 days', new DateTimeZone(APP_TIMEZONE)))->getTimestamp();

foreach ($companies as $company) {
    $nextStmt->execute([
        'user_id' => $user['id'],
        'company_id' => $company['id'],
    ]);
    $nextActivity = $nextStmt->fetch() ?: null;

    if ($withinSevenDays) {
        if (!$nextActivity) {
            continue;
        }

        $dueTs = (new DateTimeImmutable($nextActivity['due_at'], new DateTimeZone(APP_TIMEZONE)))->getTimestamp();
        if ($dueTs > $sevenDayLimit) {
            continue;
        }
    }

    $state = $nextActivity ? deadline_state($nextActivity['due_at']) : 'none';
    $isRejected = (string)$company['company_status'] === '不合格';
    $isFavorite = (int)($company['is_favorite'] ?? 0) === 1;
    $sortTs = $nextActivity
        ? (new DateTimeImmutable($nextActivity['due_at'], new DateTimeZone(APP_TIMEZONE)))->getTimestamp()
        : PHP_INT_MAX;

    $cards[] = [
        'company' => $company,
        'activity' => $nextActivity,
        'state' => $state,
        'is_rejected' => $isRejected,
        'is_favorite' => $isFavorite,
        'sort_ts' => $sortTs,
    ];

    $stats['total']++;
    if ($state === 'expired') {
        $stats['expired']++;
    } elseif ($state === 'urgent') {
        $stats['urgent']++;
    } elseif ($state === 'none') {
        $stats['empty']++;
    }
}

// 会社ごとの「次にやること」が近い順に並べ、未登録の会社は最後に置く。
usort($cards, static function (array $a, array $b): int {
    if ($a['is_rejected'] !== $b['is_rejected']) {
        return $a['is_rejected'] ? 1 : -1;
    }

    if ($a['is_favorite'] !== $b['is_favorite']) {
        return $a['is_favorite'] ? -1 : 1;
    }

    if ($a['sort_ts'] === $b['sort_ts']) {
        return strcmp((string)$a['company']['company_name'], (string)$b['company']['company_name']);
    }

    return $a['sort_ts'] <=> $b['sort_ts'];
});

$pageTitle = 'ダッシュボード';
require_once __DIR__ . '/PHP/includes/header.php';
?>
<section class="dashboard-hero">
    <div>
        <p class="eyebrow">Dashboard</p>
        <h1>選考の次の一手を確認</h1>
    </div>
    <a class="button button-primary" href="<?= e(url_for('/PHP/company/create.php')) ?>">会社を追加</a>
</section>

<section class="stats-grid" aria-label="Summary">
    <div class="stat-box">
        <span>表示中</span>
        <strong><?= e($stats['total']) ?></strong>
    </div>
    <div class="stat-box">
        <span>期限切れ</span>
        <strong><?= e($stats['expired']) ?></strong>
    </div>
    <div class="stat-box">
        <span>3日以内</span>
        <strong><?= e($stats['urgent']) ?></strong>
    </div>
    <div class="stat-box">
        <span>次の予定なし</span>
        <strong><?= e($stats['empty']) ?></strong>
    </div>
</section>

<section class="filter-bar">
    <form class="filter-form" action="<?= e(url_for('/index.php')) ?>" method="get">
        <label>
            <span>会社名</span>
            <select name="company_id">
                <option value="">すべて</option>
                <?php foreach ($companyOptions as $option): ?>
                    <option value="<?= e($option['id']) ?>"<?= (int)$option['id'] === $selectedCompanyId ? ' selected' : '' ?>>
                        <?= e($option['company_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>選考状況</span>
            <select name="status">
                <option value="">すべて</option>
                <?php foreach (company_status_options() as $option): ?>
                    <option value="<?= e($option) ?>"<?= selected_attr($statusFilter, $option) ?>><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="checkbox-label">
            <input type="checkbox" name="within7" value="1"<?= $withinSevenDays ? ' checked' : '' ?>>
            <span>7日以内のみ</span>
        </label>

        <button class="button button-secondary" type="submit">絞り込む</button>
        <a class="button button-ghost" href="<?= e(url_for('/index.php')) ?>">解除</a>
    </form>
</section>

<?php if (!$cards): ?>
    <section class="empty-dashboard">
        <h2>会社がまだ登録されていません</h2>
        <p>最初の会社を追加して、締切と次の行動を管理しましょう。</p>
        <a class="button button-primary" href="<?= e(url_for('/PHP/company/create.php')) ?>">会社を追加</a>
    </section>
<?php else: ?>
    <section class="company-grid" aria-label="Company list">
        <?php foreach ($cards as $card): ?>
            <?php
            $company = $card['company'];
            $activity = $card['activity'];
            $state = $card['state'];
            $cardClass = $card['is_rejected'] ? 'company-card company-rejected' : 'company-card deadline-' . $state;
            $favoriteLabel = $card['is_favorite'] ? 'お気に入りを解除' : 'お気に入りに追加';
            ?>
            <article class="<?= e($cardClass) ?>">
                <div class="card-topline">
                    <div class="card-status-group">
                        <span class="status-pill"><?= e($company['company_status']) ?></span>
                        <form class="favorite-form" action="<?= e(url_for('/PHP/company/toggle_favorite.php')) ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($company['id']) ?>">
                            <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? url_for('/index.php')) ?>">
                            <button class="favorite-button<?= $card['is_favorite'] ? ' is-active' : '' ?>" type="submit" aria-label="<?= e($favoriteLabel) ?>" title="<?= e($favoriteLabel) ?>">
                                <?= $card['is_favorite'] ? '★' : '☆' ?>
                            </button>
                        </form>
                    </div>
                    <span class="deadline-badge"><?= e($activity ? deadline_label($activity['due_at']) : '次の予定なし') ?></span>
                </div>

                <h2><?= e($company['company_name']) ?></h2>
                <p class="muted"><?= e($company['industry'] ?: '業界未設定') ?></p>

                <div class="next-action">
                    <?php if ($activity): ?>
                        <span><?= e($activity['activity_type']) ?></span>
                        <strong><?= e($activity['title']) ?></strong>
                        <time datetime="<?= e($activity['due_at']) ?>"><?= e(format_datetime_jp($activity['due_at'])) ?></time>
                    <?php else: ?>
                        <span>Next</span>
                        <strong>予定は登録されていません</strong>
                        <time>-</time>
                    <?php endif; ?>
                </div>

                <div class="card-actions">
                    <?php if ($company['official_url']): ?>
                        <a class="button button-small button-secondary" href="<?= e($company['official_url']) ?>" target="_blank" rel="noopener noreferrer">公式サイト</a>
                    <?php endif; ?>
                    <a class="button button-small" href="<?= e(url_for('/PHP/company/detail.php?id=' . $company['id'])) ?>">詳細</a>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
<?php require_once __DIR__ . '/PHP/includes/footer.php'; ?>
