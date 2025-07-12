<?php
// on/index.php (النسخة الصحيحة والنهائية 100%)

// 1. الإعدادات الأساسية
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ✨ التعريف الصحيح والنهائي: المسار الأساسي هو المجلد الحالي الذي يوجد به هذا الملف ✨
define('ROOT_PATH', __DIR__);

// 2. تضمين الملفات الأساسية
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/src/core/functions.php';
require_once ROOT_PATH . '/src/core/db_functions.php'; 
require_once ROOT_PATH . '/src/libraries/Database.php';

// 3. تحديد الصفحة المطلوبة
$page = $_GET['page'] ?? 'dashboard';

// 4. جدار الحماية
require_once ROOT_PATH . '/app/security.php';

// 5. معالجة الطلبات أو عرض الواجهات
$handler_pages = [
    'handle_login', 'logout', 'users/delete', 'properties/delete', 'contracts/delete', 
    'documents/delete', 'roles/delete', 'permissions/delete', 'permissions/delete_group',
    'settings/delete_lookup_option', 'settings/delete_lookup_group', 'archive/restore',
    'archive/force_delete', 'archive/batch_action', 'properties/batch_action',
    'properties/handle_batch_edit', 'properties/handle_batch_add'
];

$is_ajax_request = ($page !== 'handle_login' && strpos($page, 'handle_') !== false) || strpos($page, '_ajax') !== false;

if ($is_ajax_request || in_array($page, $handler_pages)) {
    define('IS_HANDLER', true);
    require_once ROOT_PATH . '/app/request_handler.php';
} else {
    $allowed_pages = require ROOT_PATH . '/routes/web.php';
    require_once ROOT_PATH . '/app/view_renderer.php';
}
?>