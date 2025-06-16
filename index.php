<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/core/functions.php';

$page = $_GET['page'] ?? 'dashboard';

$allowed_pages = [
    'dashboard'     => ['path' => 'dashboard/dashboard_view.php', 'title' => 'لوحة التحكم'],
    'branches'      => ['path' => 'branches/branches_view.php', 'title' => 'إدارة الفروع'],
    'branches/add'  => ['path' => 'branches/add_view.php', 'title' => 'إضافة فرع'],
];

$page_path = null;
$page_title = "الصفحة غير موجودة";
if (isset($allowed_pages[$page])) {
    $page_path = __DIR__ . '/src/modules/' . $allowed_pages[$page]['path'];
    $page_title = $allowed_pages[$page]['title'];
}

ob_start();
if (isset($_GET['view_only'])) { // <-- هذا هو الشرط الجديد والمهم
    if ($page_path && file_exists($page_path)) { require $page_path; }
} else {
    if ($page_path && file_exists($page_path)) { require $page_path; } 
    else { http_response_code(404); echo "<h1>404 - Page Not Found</h1>"; }
}
$page_content = ob_get_clean();

// إذا كان الطلب لمحتوى جزئي فقط (لـ AJAX)، اطبعه واخرج.
if (isset($_GET['view_only'])) {
    echo $page_content;
    exit();
}

// وإلا، قم بعرض القالب الكامل
require __DIR__ . '/templates/layout.php';
