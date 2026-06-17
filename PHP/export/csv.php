<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$user = current_user();
$pdo = db();

$stmt = $pdo->prepare(
    'SELECT
        c.company_name,
        c.industry,
        c.official_url,
        c.company_status,
        c.is_favorite,
        c.note AS company_note,
        c.created_at AS company_created_at,
        c.updated_at AS company_updated_at,
        a.activity_type,
        a.title AS activity_title,
        a.description AS activity_description,
        a.due_at,
        a.activity_status,
        a.priority,
        a.created_at AS activity_created_at,
        a.updated_at AS activity_updated_at
     FROM companies c
     LEFT JOIN activities a
       ON a.company_id = c.id
      AND a.user_id = c.user_id
     WHERE c.user_id = :user_id
     ORDER BY
        c.is_favorite DESC,
        c.company_name ASC,
        a.due_at ASC,
        a.id ASC'
);
$stmt->execute(['user_id' => $user['id']]);

$filename = 'senkado_backup_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$output = fopen('php://output', 'w');

// Excel で日本語が文字化けしにくいよう UTF-8 BOM を付与する。
fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, [
    '会社名',
    '業界・分類',
    '公式サイト',
    '選考状況',
    'お気に入り',
    '会社メモ',
    '会社作成日時',
    '会社更新日時',
    '予定種類',
    '予定名',
    '予定詳細',
    '期限',
    '予定状態',
    '優先度',
    '予定作成日時',
    '予定更新日時',
]);

while ($row = $stmt->fetch()) {
    fputcsv($output, [
        $row['company_name'],
        $row['industry'],
        $row['official_url'],
        $row['company_status'],
        (int)$row['is_favorite'] === 1 ? 'はい' : 'いいえ',
        $row['company_note'],
        $row['company_created_at'],
        $row['company_updated_at'],
        $row['activity_type'],
        $row['activity_title'],
        $row['activity_description'],
        $row['due_at'],
        $row['activity_status'],
        $row['priority'],
        $row['activity_created_at'],
        $row['activity_updated_at'],
    ]);
}

fclose($output);
exit;
