<?php
declare(strict_types=1);

$activity = $activity ?? [
    'company_id' => '',
    'activity_type' => 'その他',
    'title' => '',
    'description' => '',
    'due_at' => '',
    'activity_status' => '未着手',
    'priority' => '普通',
];
?>
<input type="hidden" name="company_id" value="<?= e($activity['company_id'] ?? '') ?>">

<label>
    <span>種類</span>
    <select name="activity_type">
        <?php foreach (activity_type_options() as $option): ?>
            <option value="<?= e($option) ?>"<?= selected_attr($activity['activity_type'] ?? 'その他', $option) ?>><?= e($option) ?></option>
        <?php endforeach; ?>
    </select>
</label>

<label>
    <span>優先度</span>
    <select name="priority">
        <?php foreach (priority_options() as $option): ?>
            <option value="<?= e($option) ?>"<?= selected_attr($activity['priority'] ?? '普通', $option) ?>><?= e($option) ?></option>
        <?php endforeach; ?>
    </select>
</label>

<label class="span-2">
    <span>予定名</span>
    <input type="text" name="title" maxlength="150" required value="<?= e($activity['title'] ?? '') ?>">
</label>

<label>
    <span>期限</span>
    <input type="datetime-local" name="due_at" required value="<?= e(datetime_local_value($activity['due_at'] ?? null)) ?>">
</label>

<label>
    <span>状態</span>
    <select name="activity_status">
        <?php foreach (activity_status_options() as $option): ?>
            <option value="<?= e($option) ?>"<?= selected_attr($activity['activity_status'] ?? '未着手', $option) ?>><?= e($option) ?></option>
        <?php endforeach; ?>
    </select>
</label>

<label class="span-2">
    <span>詳細・リンク</span>
    <textarea name="description" rows="7" placeholder="準備内容や関連URLを入力"><?= e($activity['description'] ?? '') ?></textarea>
</label>
