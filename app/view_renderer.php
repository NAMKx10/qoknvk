<?php
/**
 * app/view_renderer.php
 * 
 * محرك عرض الصفحات والواجهات (Views).
 * وظيفته تحديد ملف العرض المناسب بناءً على الرابط المطلوب
 * وعرضه إما كصفحة كاملة أو كجزء من نافذة منبثقة.
 */

// هذه المتغيرات تأتي من index.php
global $page, $allowed_pages, $public_pages, $pdo;

$page_path_suffix = $allowed_pages[$page]['path'] ?? null;
$page_title = $allowed_pages[$page]['title'] ?? 'الصفحة غير موجودة';

// 1. حالة الصفحات العامة (مثل صفحة تسجيل الدخول)
if (in_array($page, $public_pages) && $page !== 'handle_login') {
    $page_path = __DIR__ . '/../src/modules/' . $page_path_suffix;
    if ($page_path && file_exists($page_path)) {
        require $page_path;
    } else {
        http_response_code(404);
        echo "404 - Page not found.";
    }
} 
// 2. حالة عرض المحتوى في نافذة منبثقة فقط
elseif (isset($_GET['view_only'])) {
    $page_path = __DIR__ . '/../src/modules/' . $page_path_suffix;
    if ($page_path && file_exists($page_path)) {
        require $page_path;
    } else {
        http_response_code(404);
        echo "404 - Modal content not found.";
    }
} 
// 3. الحالة الافتراضية: عرض الصفحة الكاملة داخل التصميم الرئيسي
else {
    ob_start(); // ابدأ بالتقاط المخرجات لوضعها في المحتوى
    
    $page_path = __DIR__ . '/../src/modules/' . $page_path_suffix;
    if ($page_path && file_exists($page_path)) {
        require $page_path;
    } else {
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1><p>The page '{$page}' was not found in the allowed routes.</p>";
    }
    
    $page_content = ob_get_clean(); // احصل على محتوى الصفحة
    require __DIR__ . '/../templates/layout.php'; // قم بتضمين التصميم الرئيسي
}

?>