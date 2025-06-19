<?php
require_once __DIR__ . '/config/database.php';
// يمكنك إضافة التحقق من تسجيل الدخول هنا

$template = $_GET['template'] ?? '';
$allowed_templates = [
    'property_profile_print' => __DIR__ . '/src/modules/reports/property_profile_print_view.php',
    // سنضيف بقية قوالب الطباعة هنا
];

if (isset($allowed_templates[$template]) && file_exists($allowed_templates[$template])) {
    require_once $allowed_templates[$template];
} else {
    die('Template not found.');
}