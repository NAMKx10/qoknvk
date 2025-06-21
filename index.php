
<?php
// =================================================================
// INDEX.PHP - المسار الثاني (TABLER) - النسخة النهائية الكاملة
// =================================================================

// 1. الإعدادات الأساسية
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 2. تضمين الملفات الأساسية
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/core/functions.php';

// 3. التوجيه (Routing)
$page = $_GET['page'] ?? 'dashboard';

// --- معالجة طلبات AJAX أولاً ---
if (strpos($page, 'handle_') !== false) {
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => false, 'message' => 'حدث خطأ غير معروف.'];
    
    try {
        // --- Branches AJAX Handler ---
        if ($page === 'branches/handle_add' || $page === 'branches/handle_edit') {
            $is_add = ($page === 'branches/handle_add');
            $fields = ['branch_name', 'branch_code', 'branch_type', 'registration_number', 'tax_number', 'phone', 'email', 'address', 'notes'];
            $sql_fields = implode(', ', $fields);
            $sql_placeholders = implode(', ', array_fill(0, count($fields), '?'));
            $params = [];
            foreach ($fields as $field) { $params[] = $_POST[$field] ?? null; }
            
            if ($is_add) {
                $sql = "INSERT INTO branches ($sql_fields) VALUES ($sql_placeholders)";
            } else {
                $update_fields = "branch_name = ?, branch_code = ?, branch_type = ?, registration_number = ?, tax_number = ?, phone = ?, email = ?, address = ?, notes = ?, status = ?";
                $sql = "UPDATE branches SET {$update_fields} WHERE id = ?";
                $params[] = $_POST['status'] ?? 'نشط';
                $params[] = $_POST['id'];
            }
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) { $response = ['success' => true, 'message' => 'تمت العملية بنجاح.']; }
        }
        // --- Properties AJAX Handler ---
        elseif ($page === 'properties/handle_add' || $page === 'properties/handle_edit') {
            $is_add = ($page === 'properties/handle_add');
            $fields = ['branch_id', 'property_name', 'property_code', 'property_type', 'ownership_type', 'owner_name', 'deed_number', 'property_value', 'district', 'city', 'area', 'notes'];
            $params = [];
            foreach ($fields as $field) { $params[] = $_POST[$field] ?? null; }
            
            if ($is_add) {
                $sql_fields = implode(', ', $fields) . ', status';
                $sql_placeholders = implode(', ', array_fill(0, count($fields) + 1, '?'));
                $params[] = $_POST['status'] ?? 'نشط';
                $sql = "INSERT INTO properties ($sql_fields) VALUES ($sql_placeholders)";
            } else {
                $update_fields = "branch_id = ?, property_name = ?, property_code = ?, property_type = ?, ownership_type = ?, owner_name = ?, deed_number = ?, property_value = ?, district = ?, city = ?, area = ?, notes = ?, status = ?";
                $sql = "UPDATE properties SET {$update_fields} WHERE id = ?";
                $params[] = $_POST['status'] ?? 'نشط';
                $params[] = $_POST['id'];
            }
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) { $response = ['success' => true, 'message' => 'تمت العملية بنجاح.']; }
        }

        // --- Owners AJAX Handler ---
elseif ($page === 'owners/handle_add' || $page === 'owners/handle_edit') {
    $is_add = ($page === 'owners/handle_add');
    $pdo->beginTransaction();

    $fields = ['owner_name', 'owner_type', 'owner_code', 'id_number', 'mobile', 'email', 'notes'];
    $params = [];
    foreach ($fields as $field) { $params[] = $_POST[$field] ?? null; }
    
    if ($is_add) {
        $sql = "INSERT INTO owners (owner_name, owner_type, owner_code, id_number, mobile, email, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $owner_id = $pdo->lastInsertId();
        
        // ربط الفروع
        if (!empty($_POST['branches'])) {
            $branch_sql = "INSERT INTO owner_branches (owner_id, branch_id) VALUES (?, ?)";
            $branch_stmt = $pdo->prepare($branch_sql);
            foreach ($_POST['branches'] as $branch_id) {
                $branch_stmt->execute([$owner_id, $branch_id]);
            }
        }
    } else {
        $update_fields = "owner_name=?, owner_type=?, owner_code=?, id_number=?, mobile=?, email=?, notes=?, status=?";
        $sql = "UPDATE owners SET {$update_fields} WHERE id = ?";
        $params[] = $_POST['status'] ?? 'نشط';
        $params[] = $_POST['id'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    $pdo->commit();
    $response = ['success' => true, 'message' => 'تمت العملية بنجاح.'];
}
elseif ($page === 'owners/handle_update_branches') {
    $owner_id = $_POST['owner_id'];
    $branches = $_POST['branches'] ?? [];
    
    $pdo->beginTransaction();
    // حذف الروابط القديمة
    $delete_stmt = $pdo->prepare("DELETE FROM owner_branches WHERE owner_id = ?");
    $delete_stmt->execute([$owner_id]);
    
    // إضافة الروابط الجديدة
    if (!empty($branches)) {
        $insert_stmt = $pdo->prepare("INSERT INTO owner_branches (owner_id, branch_id) VALUES (?, ?)");
        foreach ($branches as $branch_id) {
            $insert_stmt->execute([$owner_id, $branch_id]);
        }
    }
    $pdo->commit();
    $response = ['success' => true, 'message' => 'تم تحديث الفروع بنجاح.'];
}

        // --- Settings (Lookups) AJAX Handlers ---
    elseif ($page === 'settings/handle_add_lookup_group_ajax') {
        $pdo->beginTransaction();
        try {
            // إضافة سجل لاسم المجموعة نفسها
            $sql = "INSERT INTO lookup_options (group_key, option_key, option_value) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['group_key'], $_POST['group_key'], $_POST['option_value']]);
            $pdo->commit();
            $response = ['success' => true, 'message' => 'تمت إضافة المجموعة بنجاح.'];
        } catch (Exception $e) {
            $pdo->rollBack();
            $response['message'] = $e->getMessage();
        }
    }
    elseif ($page === 'settings/handle_edit_lookup_group_ajax') {
        $pdo->beginTransaction();
        try {
            // تحديث كل الخيارات التي تنتمي للمجموعة القديمة
            $sql_update_children = "UPDATE lookup_options SET group_key = ? WHERE group_key = ?";
            $stmt_update_children = $pdo->prepare($sql_update_children);
            $stmt_update_children->execute([$_POST['new_group_key'], $_POST['original_group_key']]);

            // تحديث سجل اسم المجموعة نفسه
            $sql_update_parent = "UPDATE lookup_options SET option_key = ?, option_value = ? WHERE group_key = ? AND id = (SELECT id FROM (SELECT id FROM lookup_options WHERE group_key = ? AND option_key = ?) AS x)";
            $stmt_update_parent = $pdo->prepare($sql_update_parent);
            $stmt_update_parent->execute([$_POST['new_group_key'], $_POST['new_option_value'], $_POST['new_group_key'], $_POST['new_group_key'], $_POST['new_group_key']]);

            $pdo->commit();
            $response = ['success' => true, 'message' => 'تم تحديث المجموعة بنجاح.'];
        } catch (Exception $e) {
            $pdo->rollBack();
            $response['message'] = $e->getMessage();
        }
    }
    elseif ($page === 'settings/handle_add_lookup_option_ajax') {
        $option_key = $_POST['option_key'] ?: str_replace(' ', '_', trim(strtolower($_POST['option_value'])));
        $sql = "INSERT INTO lookup_options (group_key, option_key, option_value) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$_POST['group_key'], $option_key, $_POST['option_value']])) {
            $response = ['success' => true, 'message' => 'تم إضافة الخيار بنجاح.'];
        }
    }
    elseif ($page === 'settings/handle_edit_lookup_option_ajax') {
        $pdo->beginTransaction();
        try {
            // --- 1. تحديث البيانات الأساسية ---
            $sql = "UPDATE lookup_options SET option_value = ?, option_key = ?, color = ?, bg_color = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['option_value'], $_POST['option_key'],
                $_POST['color'] ?? '#ffffff', $_POST['bg_color'] ?? '#6c757d', $_POST['id']
            ]);

            // --- 2. تحديث مخطط الحقول المخصصة إذا كان موجوداً ---
            if (isset($_POST['custom_fields'])) {
                $filtered_fields = array_filter($_POST['custom_fields'], function($field) {
                    return !empty($field['label']) && !empty($field['name']);
                });
                
                $schema_json = json_encode(array_values($filtered_fields), JSON_UNESCAPED_UNICODE);
                
                $schema_sql = "UPDATE lookup_options SET custom_fields_schema = ? WHERE id = ?";
                $schema_stmt = $pdo->prepare($schema_sql);
                $schema_stmt->execute([$schema_json, $_POST['id']]);
            }
            
            $pdo->commit();
            $response = ['success' => true, 'message' => 'تم تحديث الخيار بنجاح.'];
        } catch (Exception $e) {
            $pdo->rollBack();
            $response['message'] = 'فشل في تحديث الخيار: ' . $e->getMessage();
        }
    }

    // --- (جديد) معالج حذف خيار واحد ---
    elseif ($page === 'settings/delete_lookup_option') {
        if (isset($_GET['id'])) {
            $sql = "UPDATE lookup_options SET deleted_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_GET['id']]);
        }
        header("Location: index.php?page=settings/lookups");
        exit();
    }
    // --- (جديد) معالج حذف مجموعة كاملة ---
    elseif ($page === 'settings/delete_lookup_group') {
        if (isset($_GET['group'])) {
            // حذف كل الخيارات التابعة للمجموعة + سجل المجموعة نفسه
            $sql = "UPDATE lookup_options SET deleted_at = NOW() WHERE group_key = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_GET['group']]);
        }
        header("Location: index.php?page=settings/lookups");
        exit();
    }

// --- (جديد) معالج جلب مخطط الحقول المخصصة ---
elseif ($page === 'documents/get_custom_fields_schema_ajax') {
    $type_key = $_GET['document_type'] ?? '';
    $sql = "SELECT custom_fields_schema FROM lookup_options WHERE group_key = 'document_type' AND option_key = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$type_key]);
    $schema_json = $stmt->fetchColumn();
    
    header('Content-Type: application/json; charset=utf-8');
    echo $schema_json ?: '[]';
    exit(); // مهم جداً الخروج بعد طباعة الـ JSON
}

// --- (جديد) معالج حفظ الوثيقة الجديدة ---
elseif ($page === 'documents/handle_add') {
    try {
        $details_json = isset($_POST['details']) ? json_encode($_POST['details'], JSON_UNESCAPED_UNICODE) : null;
        
        $sql = "INSERT INTO documents (document_type, document_number, issue_date, expiry_date, details) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['document_type'],
            $_POST['document_number'],
            $_POST['issue_date'] ?: null,
            $_POST['expiry_date'] ?: null,
            $details_json
        ]);
        $response = ['success' => true, 'message' => 'تمت إضافة الوثيقة بنجاح.'];
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

elseif ($page === 'documents/handle_edit') {
    try {
        $details_json = isset($_POST['details']) ? json_encode($_POST['details'], JSON_UNESCAPED_UNICODE) : null;
        
        $sql = "UPDATE documents SET document_number = ?, issue_date = ?, expiry_date = ?, details = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['document_number'],
            $_POST['issue_date'] ?: null,
            $_POST['expiry_date'] ?: null,
            $details_json,
            $_POST['id']
        ]);
        $response = ['success' => true, 'message' => 'تم تحديث الوثيقة بنجاح.'];
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}


    } catch (PDOException $e) {
        $response['message'] = 'خطأ في قاعدة البيانات: ' . $e->getMessage();
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}

// --- عرض الصفحات ---
$allowed_pages = [
    'dashboard'         => ['path' => 'dashboard/dashboard_view.php', 'title' => 'لوحة التحكم'],
    'about'             => ['path' => 'about/about_view.php', 'title' => 'حول النظام'],
    // الإدارة الأساسية
    'owners'            => ['path' => 'owners/owners_view.php', 'title' => 'إدارة الملاك'],
    'owners/add'        => ['path' => 'owners/add_view.php', 'title' => 'إضافة مالك'],
    'owners/edit'       => ['path' => 'owners/edit_view.php', 'title' => 'تعديل مالك'],
    'owners/branches_modal' => ['path' => 'owners/branches_modal_view.php', 'title' => 'إدارة فروع المالك'],
    'branches'          => ['path' => 'branches/branches_view.php', 'title' => 'إدارة الفروع'],
    'branches/add'      => ['path' => 'branches/add_view.php', 'title' => 'إضافة فرع'],
    'branches/edit'     => ['path' => 'branches/edit_view.php', 'title' => 'تعديل فرع'],
    'properties'        => ['path' => 'properties/properties_view.php', 'title' => 'إدارة العقارات'],
    'properties/add'    => ['path' => 'properties/add_view.php', 'title' => 'إضافة عقار'],
    'properties/edit'   => ['path' => 'properties/edit_view.php', 'title' => 'تعديل عقار'],
    'units'             => ['path' => 'units/units_view.php', 'title' => 'إدارة الوحدات'],
    'units/add'         => ['path' => 'units/add_view.php', 'title' => 'إضافة وحدة'],
    'clients'           => ['path' => 'clients/clients_view.php', 'title' => 'إدارة العملاء'],
    'clients/add'       => ['path' => 'clients/add_view.php', 'title' => 'إضافة عميل'],
    'suppliers'         => ['path' => 'suppliers/suppliers_view.php', 'title' => 'إدارة الموردين'],
    'suppliers/add'     => ['path' => 'suppliers/add_view.php', 'title' => 'إضافة مورد'],
    // العقود والمالية
    'contracts'         => ['path' => 'contracts/contracts_view.php', 'title' => 'عقود الإيجار'],
    'contracts/view'    => ['path' => 'contracts/view_view.php', 'title' => 'تفاصيل العقد'],
    'supply_contracts'  => ['path' => 'supply_contracts/supply_contracts_view.php', 'title' => 'عقود التوريد'],
    'supply_contracts/view' => ['path' => 'supply_contracts/view_view.php', 'title' => 'تفاصيل العقد'],
    // إدارة النظام
    'documents'         => ['path' => 'documents/documents_view.php', 'title' => 'إدارة الوثائق'],
    'documents/add'     => ['path' => 'documents/add_view.php', 'title' => 'إضافة وثيقة'],
    'documents/edit'    => ['path' => 'documents/edit_view.php', 'title' => 'تعديل وثيقة'],
    'users'             => ['path' => 'users/users_view.php', 'title' => 'إدارة المستخدمين'],
    'roles'             => ['path' => 'roles/roles_view.php', 'title' => 'إدارة الأدوار'],
    'permissions'       => ['path' => 'permissions/permissions_view.php', 'title' => 'إدارة الصلاحيات'],
    'archive'           => ['path' => 'archive/archive_view.php', 'title' => 'الأرشيف'],
    'settings/lookups'              => ['path' => 'settings/lookups_view.php', 'title' => 'تهيئة المدخلات'],
    'settings/add_lookup_group'     => ['path' => 'settings/add_lookup_group_view.php', 'title' => 'إضافة مجموعة'],
    'settings/edit_lookup_group'    => ['path' => 'settings/edit_lookup_group_view.php', 'title' => 'تعديل مجموعة'],
    'settings/add_lookup_option'    => ['path' => 'settings/add_lookup_option_view.php', 'title' => 'إضافة خيار'],
    'settings/edit_lookup_option'   => ['path' => 'settings/edit_lookup_option_view.php', 'title' => 'تعديل خيار'],

    
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