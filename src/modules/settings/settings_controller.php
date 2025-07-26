<?php
// src/modules/settings/settings_controller.php (النسخة النهائية الكاملة)

global $pdo;

// 1. جلب كل الإعدادات
$settings_stmt = $pdo->query("SELECT * FROM settings ORDER BY display_order ASC");
$settings_list = $settings_stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. تجميع الإعدادات في تبويبات
$grouped_settings = [];
foreach ($settings_list as $setting) {
    $grouped_settings[$setting['group_key']][] = $setting;
}

// 3. تجهيز بيانات إضافية للواجهة
$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

// 4. استدعاء الواجهة
require_once __DIR__ . '/settings_view.php';