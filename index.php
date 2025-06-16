<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/core/functions.php';

$page = $_GET['page'] ?? 'dashboard';

// --- معالجة الطلبات أولاً (الطريقة التقليدية) ---
if ($page === 'branches/handle_add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sql = "INSERT INTO branches (branch_name, branch_code, branch_type, registration_number, tax_number, phone, email, address, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $_POST['branch_name'], $_POST['branch_code'], $_POST['branch_type'],
            $_POST['registration_number'], $_POST['tax_number'], $_POST['phone'],
            $_POST['email'], $_POST['address'], $_POST['notes']
        ];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // إعادة التوجيه إلى صفحة الفروع بعد الحفظ
        header("Location: index.php?page=branches");
        exit();
    }
}
// --- يمكنك إضافة معالجات أخرى هنا ---

// --- عرض الصفحات ---
$allowed_pages = [
    'dashboard'     => ['path' => 'dashboard/dashboard_view.php', 'title' => 'لوحة التحكم'],
    'branches'      => ['path' => 'branches/branches_view.php', 'title' => 'إدارة الفروع'],
    'branches/add'  => ['path' => 'branches/add_view.php', 'title' => 'إضافة فرع'],
    'properties'    => ['path' => 'properties/properties_view.php', 'title' => 'إدارة العقارات'],
    'properties/add' => ['path' => 'properties/add_view.php', 'title' => 'إضافة عقار'],
    'units'          => ['path' => 'units/units_view.php', 'title' => 'إدارة الوحدات'], 
    'units/add'      => ['path' => 'units/add_view.php', 'title' => 'إضافة وحدة'],
    'clients'       => ['path' => 'clients/clients_view.php', 'title' => 'إدارة العملاء'], 
    'clients/add'   => ['path' => 'clients/add_view.php', 'title' => 'إضافة عميل'], 
    'clients/branches_modal' => ['path' => 'clients/branches_modal_view.php', 'title' => 'الفروع المرتبطة'],
    'suppliers'        => ['path' => 'suppliers/suppliers_view.php', 'title' => 'إدارة الموردين'], // <-- جديد
    'suppliers/add'    => ['path' => 'suppliers/add_view.php', 'title' => 'إضافة مورد'], // <-- جديد
    'suppliers/branches_modal' => ['path' => 'suppliers/branches_modal_view.php', 'title' => 'الفروع المرتبطة'],
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
