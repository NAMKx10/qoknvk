<?php
// app/view_renderer.php (نسخة نهائية ونظيفة)

global $page, $allowed_pages, $pdo;

$page_path_info = $allowed_pages[$page] ?? null;
$page_title = $page_path_info['title'] ?? 'الصفحة غير موجودة';

$public_pages = ['login', 'forgot-password', 'handle_login', 'logout'];

ob_start();

$page_path = ROOT_PATH . '/src/modules/' . ($page_path_info['path'] ?? '');

if (isset($_GET['view_only'])) { // طلبات النوافذ المنبثقة
    if ($page_path_info && file_exists($page_path)) {
        require $page_path;
    } else {
        http_response_code(404);
        echo "404 - Modal content not found.";
    }
    // لا نلتقط المخرجات هنا، فقط نخرجها مباشرة
    echo ob_get_clean(); 
} else { // طلبات الصفحات الكاملة
    if ($page_path_info && file_exists($page_path)) {
        require $page_path;
    } else {
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
    }
    
    $page_content = ob_get_clean();
    
    // اعرض القالب الكامل فقط إذا لم تكن صفحة عامة
    if (!in_array($page, $public_pages)) {
        require ROOT_PATH . '/templates/layout.php';
    } else {
        echo $page_content; // اعرض الصفحة العامة مباشرة
    }
}
?>