<?php

// تضمين إعدادات قاعدة البيانات
require_once __DIR__ . '/config/database.php';

// يمكنك إضافة التحقق من تسجيل الدخول هنا

// الحصول على اسم القالب من الرابط (GET)
$template = $_GET['template'] ?? '';

// مصفوفة القوالب المسموحة مع مساراتها
$allowed_templates = [
    'property_profile_print' => __DIR__ . '/src/modules/reports/property_profile_print_view.php',
    'branch_profile_print'   => __DIR__ . '/src/modules/reports/branch_profile_print_view.php',
    'client_profile_print'   => __DIR__ . '/src/modules/reports/client_profile_print_view.php',
    'supplier_profile_print'   => __DIR__ . '/src/modules/reports/supplier_profile_print_view.php',
    'unit_profile_print'   => __DIR__ . '/src/modules/reports/unit_profile_print_view.php',
    // سنضيف بقية قوالب الطباعة هنا
];

// التحقق من وجود القالب ضمن القوالب المسموحة ووجود الملف فعلياً
if (isset($allowed_templates[$template]) && file_exists($allowed_templates[$template])) {
    require_once $allowed_templates[$template];
} else {
    die('Template not found.');
}
