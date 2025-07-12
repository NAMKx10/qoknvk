<?php
// src/modules/settings/lookups_controller.php

global $pdo;

// جلب وتجميع كل الخيارات والمجموعات في استعلام واحد
$stmt = $pdo->query("SELECT * FROM lookup_options WHERE deleted_at IS NULL ORDER BY group_key, display_order, id");
$options = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped_options = [];
foreach ($options as $option) {
    if ($option['group_key'] === $option['option_key']) {
        // هذا هو سجل اسم المجموعة
        $grouped_options[$option['group_key']]['group_info'] = $option;
    } else {
        // هذا خيار داخل المجموعة
        $grouped_options[$option['group_key']]['options'][] = $option;
    }
}

// استدعاء الواجهة
require_once __DIR__ . '/lookups_view.php';
?>