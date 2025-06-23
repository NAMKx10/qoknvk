<?php
// ==========================================================================
// index.php (النسخة النهائية والمثالية)
// هذا الملف الآن هو "مدير المشروع"، وظيفته فقط استدعاء الأجزاء الصحيحة.
// ==========================================================================

// 1. الإعدادات الأساسية
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 2. تضمين الملفات الأساسية
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/core/functions.php';

// 3. تحديد الصفحة المطلوبة
$page = $_GET['page'] ?? 'dashboard';

// 4. استدعاء جدار الحماية (للتحقق من الجلسة والصلاحيات)
require_once __DIR__ . '/app/security.php';

// 5. استدعاء معالج الطلبات (الذي سيحتوي على كل الـ if/elseif)
// هذا سيقوم بالتعامل مع كل طلبات AJAX وإعادة التوجيه.
define('IS_HANDLER', true); // تعريف ثابت للأمان
require_once __DIR__ . '/app/request_handler.php';

// 6. استدعاء خريطة الصفحات المسموح بها
$allowed_pages = require __DIR__ . '/routes/web.php';

// 7. استدعاء محرك عرض الصفحات (لعرض الواجهات)
require_once __DIR__ . '/app/view_renderer.php';
?>