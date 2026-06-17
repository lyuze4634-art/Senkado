<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url_for(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    return rtrim(BASE_PATH, '/') . $path;
}

function redirect(string $path): void
{
    header('Location: ' . url_for($path));
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id' => (int)$_SESSION['user_id'],
        'name' => (string)($_SESSION['user_name'] ?? ''),
        'email' => (string)($_SESSION['user_email'] ?? ''),
    ];
}

function set_flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flash(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}

function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
}

function company_status_options(): array
{
    return ['準備中', '応募中', '選考中', '内定', '辞退', '不合格', '保留'];
}

function activity_status_options(): array
{
    return ['未着手', '進行中', '完了', 'キャンセル'];
}

function activity_type_options(): array
{
    return ['説明会', '面談', 'ES/履歴書', 'Webテスト', '適性検査', '一次面接', '二次面接', '最終面接', '資料提出', '結果確認', 'その他'];
}

function priority_options(): array
{
    return ['低', '普通', '高', '緊急'];
}

function valid_choice(string $value, array $options, string $default): string
{
    return in_array($value, $options, true) ? $value : $default;
}

function selected_attr(?string $current, string $value): string
{
    return $current === $value ? ' selected' : '';
}

function normalize_optional_url(?string $url): ?string
{
    $url = trim((string)$url);

    if ($url === '') {
        return null;
    }

    return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
}

function datetime_local_value(?string $mysqlDateTime): string
{
    if (!$mysqlDateTime) {
        return '';
    }

    return (new DateTimeImmutable($mysqlDateTime))->format('Y-m-d\TH:i');
}

function mysql_datetime_from_local(string $value): ?string
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value, new DateTimeZone(APP_TIMEZONE));
    $errors = DateTimeImmutable::getLastErrors();

    if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        return null;
    }

    return $date->format('Y-m-d H:i:s');
}

function format_datetime_jp(?string $mysqlDateTime): string
{
    if (!$mysqlDateTime) {
        return '-';
    }

    return (new DateTimeImmutable($mysqlDateTime))->format('Y/m/d H:i');
}

function deadline_state(?string $mysqlDateTime): string
{
    if (!$mysqlDateTime) {
        return 'none';
    }

    $now = new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE));
    $due = new DateTimeImmutable($mysqlDateTime, new DateTimeZone(APP_TIMEZONE));
    $seconds = $due->getTimestamp() - $now->getTimestamp();

    if ($seconds < 0) {
        return 'expired';
    }

    if ($seconds <= 3 * 86400) {
        return 'urgent';
    }

    if ($seconds <= 7 * 86400) {
        return 'soon';
    }

    return 'normal';
}

function deadline_label(?string $mysqlDateTime): string
{
    if (!$mysqlDateTime) {
        return '次の予定なし';
    }

    $timezone = new DateTimeZone(APP_TIMEZONE);
    $now = new DateTimeImmutable('now', $timezone);
    $due = new DateTimeImmutable($mysqlDateTime, $timezone);
    $seconds = $due->getTimestamp() - $now->getTimestamp();

    if ($seconds < 0) {
        return '期限切れ';
    }

    // 「本日/明日」は24時間差ではなく、利用者の暦日で判定する。
    $today = $now->setTime(0, 0);
    $dueDate = $due->setTime(0, 0);
    $daysUntilDue = (int)$today->diff($dueDate)->format('%r%a');

    if ($daysUntilDue === 0) {
        return '本日 ' . $due->format('H:i');
    }

    if ($daysUntilDue === 1) {
        return '明日 ' . $due->format('H:i');
    }

    return 'あと' . $daysUntilDue . '日';
}

function render_text_with_links(?string $text): string
{
    $text = trim((string)$text);

    if ($text === '') {
        return '';
    }

    $parts = preg_split('~(https?://[^\s]+)~u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $html = '';

    foreach ($parts as $part) {
        if (preg_match('~^https?://[^\s]+$~u', $part) && filter_var($part, FILTER_VALIDATE_URL)) {
            $html .= '<a href="' . e($part) . '" target="_blank" rel="noopener noreferrer">' . e($part) . '</a>';
        } else {
            $html .= e($part);
        }
    }

    return nl2br($html);
}

function ensure_upload_dir(): void
{
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
}
