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

// 3. التوجيه البسيط
$page = $_GET['page'] ?? 'dashboard';

// --- معالجة طلبات AJAX أولاً ---
if (strpos($page, 'handle_') !== false) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'حدث خطأ غير معروف.'];
    
    // يمكنك إضافة التحقق من تسجيل الدخول هنا إذا أردت
    
    try {
        // --- Branches AJAX Handler ---
        if ($page === 'branches/handle_add_ajax' || $page === 'branches/handle_edit_ajax') {
            $is_add = ($page === 'branches/handle_add_ajax');
            if ($is_add) {
                $sql = "INSERT INTO branches (branch_name, branch_code, branch_type, registration_number, tax_number, phone, email, address, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [$_POST['branch_name'], $_POST['branch_code'], $_POST['branch_type'], $_POST['registration_number'], $_POST['tax_number'], $_POST['phone'], $_POST['email'], $_POST['address'], $_POST['notes']];
            } else {
                // منطق التعديل سيأتي هنا لاحقًا
            }
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $response = ['success' => true, 'message' => 'تم الحفظ بنجاح.'];
            }
        }
        // --- يمكنك إضافة معالجات AJAX أخرى هنا باستخدام elseif ---

    } catch (PDOException $e) {
        // يمكنك تسجيل الخطأ الفعلي في ملف logs
        $response['message'] = 'خطأ في قاعدة البيانات.';
        if ($e->errorInfo[1] == 1062) { // خطأ تكرار قيمة فريدة
            $response['message'] = 'كود الفرع مستخدم بالفعل. يرجى إدخال كود آخر.';
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit(); // إنهاء التنفيذ بعد معالجة AJAX
}


// --- عرض الصفحات العادية ---
$allowed_pages = [
    'dashboard'     => ['path' => 'dashboard/dashboard_view.php', 'title' => 'لوحة التحكم'],
    'branches'      => ['path' => 'branches/branches_view.php', 'title' => 'إدارة الفروع'],
    'branches/add'  => ['path' => 'branches/add_view.php', 'title' => 'إضافة فرع'],
    'about'         => ['path' => 'about/about_view.php', 'title' => 'حول النظام'],
];

$page_path = null;
$page_title = "الصفحة غير موجودة";
if (isset($allowed_pages[$page])) {
    $page_path = __DIR__ . '/src/modules/' . $allowed_pages[$page]['path'];
    $page_title = $allowed_pages[$page]['title'];
}

ob_start();
if (isset($_GET['view_only'])) {
    if ($page_path && file_exists($page_path)) { require $page_path; }
} else {
    if ($page_path && file_exists($page_path)) { require $page_path; } 
    else { http_response_code(404); echo "<h1>404 - Page Not Found</h1>"; }
}
$page_content = ob_get_clean();

if (isset($_GET['view_only'])) {
    echo $page_content;
    exit();
}

require __DIR__ . '/templates/layout.php';
