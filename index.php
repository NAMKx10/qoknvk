<?php
// ==========================================================================
// index.php (النسخة المحدثة بعد إضافة ملف الدوال)
// ==========================================================================

// 1. الإعدادات الأساسية
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 2. تضمين الملفات الأساسية
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/core/functions.php';
require_once __DIR__ . '/src/core/db_functions.php'; // <--- ✨ هذا هو السطر الجديد ✨

// 3. تحديد الصفحة المطلوبة
$page = $_GET['page'] ?? 'dashboard';

// 4. جدار الحماية (التحقق من الجلسة والصلاحيات)
require_once __DIR__ . '/app/security.php';

// ==========================================================
// 5. معالجة الطلبات أو عرض الواجهات (الهيكل الجديد والحاسم)
// ==========================================================

// --- تعريف قائمة المعالجات التي لا تعرض واجهات ---
$handler_pages = [
    'handle_login',
    'logout',
    'properties/delete',
    'properties/batch_action',
    'properties/handle_batch_edit',
    'properties/handle_batch_add',
    'contracts/delete',
    'users/delete',
    'documents/delete',
    'roles/delete',
    'roles/handle_edit_permissions',
    'permissions/delete',
    'permissions/delete_group',
    'settings/delete_lookup_option',
    'settings/delete_lookup_group',
    'archive/restore',
    'archive/force_delete',
    'archive/batch_action',
];

// هذا الشرط يتحقق إذا كان الرابط طلب AJAX
$is_ajax_request = ($page !== 'handle_login' && strpos($page, 'handle_') !== false) || strpos($page, '_ajax') !== false;

// --- الشرط الحاسم: هل هو طلب معالجة أم طلب عرض؟ ---
if ($is_ajax_request || in_array($page, $handler_pages)) {
    // نعم، هذا طلب معالجة، لذلك نستدعي المعالج وننتهي.
    define('IS_HANDLER', true);
    require_once __DIR__ . '/app/request_handler.php';
    // لن يتم تنفيذ أي كود بعد هذا السطر لأن المعالج سيقوم بعمل exit()

} else {
    // لا، هذا طلب عرض واجهة، لذلك نستدعي محرك العرض.
    $allowed_pages = require __DIR__ . '/routes/web.php';
    require_once __DIR__ . '/app/view_renderer.php';
}
?>