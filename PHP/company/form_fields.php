<?php
declare(strict_types=1);

$company = $company ?? [
    'company_name' => '',
    'industry' => '',
    'official_url' => '',
    'company_status' => '準備中',
    'note' => '',
];
?>
<label>
    <span>会社名</span>
    <input type="text" name="company_name" maxlength="100" required value="<?= e($company['company_name'] ?? '') ?>">
</label>

<label>
    <span>業界・分類</span>
    <input type="text" name="industry" maxlength="100" value="<?= e($company['industry'] ?? '') ?>">
</label>

<label>
    <span>公式サイト</span>
    <input type="url" name="official_url" maxlength="500" placeholder="https://example.com" value="<?= e($company['official_url'] ?? '') ?>">
</label>

<label>
    <span>選考状況</span>
    <select name="company_status">
        <?php foreach (company_status_options() as $option): ?>
            <option value="<?= e($option) ?>"<?= selected_attr($company['company_status'] ?? '準備中', $option) ?>><?= e($option) ?></option>
        <?php endforeach; ?>
    </select>
</label>

<label class="span-2">
    <span>メモ</span>
    <textarea name="note" rows="6"><?= e($company['note'] ?? '') ?></textarea>
</label>
