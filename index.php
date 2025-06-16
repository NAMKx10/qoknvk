<?php
// =================================================================
// INDEX.PHP - المسار الثاني (TABLER) - النسخة النهائية الصحيحة
// =================================================================

// 1. الإعدادات الأساسية
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 2. تضمين الملفات الأساسية
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/core/functions.php';

// 3. التوجيه البسيط (يعتمد على .htaccess)
$page = $_GET['page'] ?? 'dashboard';

// قائمة بيضاء بالصفحات المسموح بها ومساراتها
$allowed_pages = [
    'dashboard' => ['path' => 'dashboard/dashboard_view.php', 'title' => 'لوحة التحكم'],
    'about'     => ['path' => 'about/about_view.php', 'title' => 'حول النظام'],
    'branches'  => ['path' => 'branches/branches_view.php', 'title' => 'إدارة الفروع'],
    'branches/add' => ['path' => 'branches/add_view.php', 'title' => 'إضافة فرع'],
    'branches/edit' => ['path' => 'branches/edit_view.php', 'title' => 'تعديل فرع'],
    // سنضيف بقية الصفحات هنا
];

// تحديد المسار والعنوان
$page_path = null;
$page_title = "الصفحة غير موجودة";
if (isset($allowed_pages[$page])) {
    $page_path = __DIR__ . '/src/modules/' . $allowed_pages[$page]['path'];
    $page_title = $allowed_pages[$page]['title'];
}

// 4. آلية عرض القالب
ob_start();
if ($page_path && file_exists($page_path)) {
    require $page_path;
} else {
    http_response_code(404);
    echo "<h1>404 - الصفحة غير موجودة</h1>";
}
$page_content = ob_get_clean();

// 5. تضمين القالب الرئيسي
require __DIR__ . '/templates/layout.php';