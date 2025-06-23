<?php
// ==========================================================================
// index.php - نقطة الدخول الرئيسية للنظام | تم الترتيب والتنظيم والشرح بالكامل
// ==========================================================================

// --------------------------------------------------------------------------
// 1. الإعدادات الأساسية
// --------------------------------------------------------------------------
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------------------------------------------------------------
// 2. تضمين الملفات الأساسية
// --------------------------------------------------------------------------
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/core/functions.php';

// --------------------------------------------------------------------------
// 3. التوجيه (Routing) وتحديد الصفحة المطلوبة
// --------------------------------------------------------------------------
$page = $_GET['page'] ?? 'dashboard';

// --------------------------------------------------------------------------
// 4. جدار الحماية والتحقق من الجلسة
// --------------------------------------------------------------------------
$public_pages = ['login', 'handle_login', 'logout'];

if (!isset($_SESSION['user_id']) && !in_array($page, $public_pages)) {
    header('Location: index.php?page=login');
    exit();
}

// تحميل الصلاحيات وفروع المستخدم مباشرة بعد تسجيل الدخول
if (isset($_SESSION['user_id']) && !isset($_SESSION['user_permissions'])) {
    $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $current_user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    if ($current_user) {
        $_SESSION['username'] = $current_user['full_name'];
        $permissions_stmt = $pdo->prepare("
            SELECT p.permission_key 
            FROM permissions p 
            JOIN role_permissions rp ON p.id = rp.permission_id 
            WHERE rp.role_id = ?
        ");
        $permissions_stmt->execute([$current_user['role_id']]);
        $_SESSION['user_permissions'] = $permissions_stmt->fetchAll(PDO::FETCH_COLUMN);

        $branches_stmt = $pdo->prepare("SELECT branch_id FROM user_branches WHERE user_id = ?");
        $branches_stmt->execute([$_SESSION['user_id']]);
        $user_branch_ids = $branches_stmt->fetchAll(PDO::FETCH_COLUMN);
        $_SESSION['user_branch_ids'] = empty($user_branch_ids) ? 'ALL' : $user_branch_ids;
    } else {
        session_destroy();
        header('Location: index.php?page=login');
        exit();
    }
}

// --------------------------------------------------------------------------
// 5. معالجة الطلبات الخاصة (Handlers & AJAX)
// --------------------------------------------------------------------------
$is_handler_request = (
    // معالجات عامة (إضافة/تعديل/حذف/AJAX)
    strpos($page, 'handle_') !== false ||
    strpos($page, '_ajax') !== false ||
    strpos($page, '/delete') !== false ||
    strpos($page, 'archive/') !== false ||
    // معالجات أدوار المستخدمين
    strpos($page, 'roles/handle_') === 0 ||
    strpos($page, 'roles/delete') === 0 ||
    // معالجات صلاحيات النظام
    strpos($page, 'permissions/handle_') === 0 ||
    $page === 'permissions/delete' ||
    $page === 'permissions/delete_group' ||
    // طلبات الخروج
    $page === 'logout'
);

if ($is_handler_request) {
        ob_start(); // ابدأ بالتقاط المخرجات لمنع الشوائب
    // إذا كان الطلب AJAX أو معالجة مباشرة
    if (
        strpos($page, 'handle_') !== false ||
        strpos($page, '_ajax') !== false ||
        strpos($page, 'add_link_ajax') !== false ||
        strpos($page, 'delete_link_ajax') !== false
    ) {
        header('Content-Type: application/json; charset=utf-8');
        $response = ['success' => false, 'message' => 'حدث خطأ غير معروف.'];
    }

    try {

        // --- (جديد) Login Handler ---
        if ($page === 'handle_login') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND deleted_at IS NULL AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // تم التحقق بنجاح، قم بإنشاء الجلسة
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php?page=dashboard"); // إعادة توجيه للوحة التحكم
                exit();
            } else {
                // فشل تسجيل الدخول
                $_SESSION['login_error'] = "اسم المستخدم أو كلمة المرور غير صحيحة.";
                header("Location: index.php?page=login");
                exit();
            }
        }

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
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => false, 'message' => 'حدث خطأ غير معروف.'];
    try {
        $pdo->beginTransaction();
        $sql = "INSERT INTO lookup_options (group_key, option_key, option_value) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['group_key'], $_POST['group_key'], $_POST['option_value']]);
        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'تمت إضافة المجموعة بنجاح.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
    echo json_encode($response);
    exit();
}

elseif ($page === 'settings/handle_edit_lookup_group_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => false, 'message' => 'حدث خطأ غير معروف.'];
    $pdo->beginTransaction();
    try {
        // تحديث كل الخيارات التي تنتمي للمجموعة القديمة
        $sql_update_children = "UPDATE lookup_options SET group_key = ? WHERE group_key = ?";
        $stmt_update_children = $pdo->prepare($sql_update_children);
        $stmt_update_children->execute([$_POST['new_group_key'], $_POST['original_group_key']]);

        // تحديث سجل اسم المجموعة نفسه
        $sql_update_parent = "UPDATE lookup_options SET option_key = ?, option_value = ? WHERE group_key = ? AND id = (SELECT id FROM (SELECT id FROM lookup_options WHERE group_key = ? AND option_key = ?) AS tmp LIMIT 1)";
        $stmt_update_parent = $pdo->prepare($sql_update_parent);
        $stmt_update_parent->execute([$_POST['new_group_key'], $_POST['new_option_value'], $_POST['new_group_key'], $_POST['new_group_key'], $_POST['new_group_key']]);
        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'تم تحديث المجموعة بنجاح.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
    echo json_encode($response);
    exit();
}

elseif ($page === 'settings/handle_add_lookup_option_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => false, 'message' => 'حدث خطأ غير معروف.'];
    try {
        $option_key = $_POST['option_key'] ?: str_replace(' ', '_', trim(strtolower($_POST['option_value'])));
        $sql = "INSERT INTO lookup_options (group_key, option_key, option_value) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$_POST['group_key'], $option_key, $_POST['option_value']])) {
            $response['success'] = true;
            $response['message'] = 'تم إضافة الخيار بنجاح.';
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
    echo json_encode($response);
    exit();
}

elseif ($page === 'settings/handle_edit_lookup_option_ajax') {
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => false, 'message' => 'حدث خطأ غير معروف.'];
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
        $response['success'] = true;
        $response['message'] = 'تم تحديث الخيار بنجاح.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
    echo json_encode($response);
    exit();
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

        // --- معالج جلب مخطط الحقول المخصصة ---
        elseif ($page === 'documents/get_custom_fields_schema_ajax') {
            $type_key = $_GET['document_type'] ?? '';
            $sql = "SELECT custom_fields_schema FROM lookup_options WHERE group_key = 'documents_type' AND option_key = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$type_key]);
            $schema_json = $stmt->fetchColumn();
            
            header('Content-Type: application/json; charset=utf-8');
            echo $schema_json ?: '[]';
            exit(); // مهم جداً
        }

        // --- (جديد) معالج حفظ الوثيقة الجديدة ---
        elseif ($page === 'documents/handle_add') {
            $pdo->beginTransaction();
            try {
                $details_json = isset($_POST['details']) ? json_encode($_POST['details'], JSON_UNESCAPED_UNICODE) : null;
                
                // 1. حفظ الوثيقة الأساسية
                $sql_doc = "INSERT INTO documents (document_type, document_name, document_number, issue_date, expiry_date, status, notes, details) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_doc = $pdo->prepare($sql_doc);
                $stmt_doc->execute([
                    $_POST['document_type'], $_POST['document_name'], $_POST['document_number'],
                    $_POST['issue_date'] ?: null, $_POST['expiry_date'] ?: null,
                    $_POST['status'], $_POST['notes'], $details_json
                ]);
                $new_doc_id = $pdo->lastInsertId();

                // 2. (جديد) حفظ الكيانات المرتبطة
                if (isset($_POST['links']) && is_array($_POST['links'])) {
                    $sql_link = "INSERT INTO entity_documents (document_id, entity_type, entity_id) VALUES (?, ?, ?)";
                    $stmt_link = $pdo->prepare($sql_link);
                    
                    foreach ($_POST['links'] as $link) {
                        if (!empty($link['entity_type']) && !empty($link['entity_id'])) {
                            $stmt_link->execute([$new_doc_id, $link['entity_type'], $link['entity_id']]);
                        }
                    }
                }
                
                $pdo->commit();
                $response = ['success' => true, 'message' => 'تمت إضافة الوثيقة وربطها بنجاح.'];

            } catch (Exception $e) {
                $pdo->rollBack();
                $response['message'] = $e->getMessage();
            }
        }
        elseif ($page === 'documents/handle_edit') {
            try {
                $details_json = isset($_POST['details']) ? json_encode($_POST['details'], JSON_UNESCAPED_UNICODE) : null;
                
                // (مُصحَّح) تم تعديل الاستعلام وعدد المتغيرات ليتطابقا تمامًا
                $sql = "UPDATE documents SET document_name = ?, document_number = ?, issue_date = ?, expiry_date = ?, details = ?, status = ?, notes = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['document_name'],
                    $_POST['document_number'],
                    $_POST['issue_date'] ?: null,
                    $_POST['expiry_date'] ?: null,
                    $details_json,
                    $_POST['status'],
                    $_POST['notes'],
                    $_POST['id']
                ]);
                $response = ['success' => true, 'message' => 'تم تحديث الوثيقة بنجاح.'];
            } catch (Exception $e) {
                $response['message'] = $e->getMessage();
            }
        }

        // --- (جديد) معالج حذف الوثيقة الجديدة ---
        elseif ($page === 'documents/delete') {
            if (isset($_GET['id'])) {
                $sql = "UPDATE documents SET deleted_at = NOW() WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_GET['id']]);
            }
            header("Location: index.php?page=documents");
            exit();
        }

        // --- (جديد) Document Linking AJAX Handlers ---
        elseif ($page === 'documents/get_entities_for_linking_ajax') {
            $type = $_GET['type'] ?? '';
            $branch_id = $_GET['branch_id'] ?? null;
            $data = [];
            $params = [];
            $sql = '';

            $branch_condition = '';
            if ($branch_id) {
                $params[] = $branch_id;
            }

            switch ($type) {
                case 'property': 
                    $sql = "SELECT id, property_name as text FROM properties WHERE deleted_at IS NULL ";
                    if ($branch_id) { $sql .= ' AND branch_id = ?'; }
                    $sql .= " ORDER BY text"; 
                    break;
                case 'owner':
                    $sql = "SELECT DISTINCT o.id, o.owner_name as text FROM owners o ";
                    if ($branch_id) { $sql .= " JOIN owner_branches ob ON o.id = ob.owner_id WHERE o.deleted_at IS NULL AND ob.branch_id = ?"; }
                    else { $sql .= " WHERE o.deleted_at IS NULL"; }
                    $sql .= " ORDER BY text";
                    break;
                case 'client':
                    $sql = "SELECT DISTINCT c.id, c.client_name as text FROM clients c ";
                    if ($branch_id) { $sql .= " JOIN client_branches cb ON c.id = cb.client_id WHERE c.deleted_at IS NULL AND cb.branch_id = ?"; }
                    else { $sql .= " WHERE c.deleted_at IS NULL"; }
                    $sql .= " ORDER BY text";
                    break;
                case 'supplier':
                    $sql = "SELECT DISTINCT s.id, s.supplier_name as text FROM suppliers s ";
                    if ($branch_id) { $sql .= " JOIN supplier_branches sb ON s.id = sb.supplier_id WHERE s.deleted_at IS NULL AND sb.branch_id = ?"; }
                    else { $sql .= " WHERE s.deleted_at IS NULL"; }
                    $sql .= " ORDER BY text";
                    break;
            }
            
            if ($sql) { 
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);
            exit();
        }
        elseif ($page === 'documents/get_linked_entities_ajax') {
            header('Content-Type: text/html; charset=utf-8');
            $doc_id = $_GET['doc_id'] ?? 0;
            
            // 1. جلب كل الروابط الأساسية
            $sql = "SELECT id, entity_type, entity_id FROM entity_documents WHERE document_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$doc_id]);
            $links = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. قاموس لترجمة نوع الكيان
            $entity_type_names = ['property' => 'عقار', 'owner' => 'مالك', 'client' => 'عميل', 'supplier' => 'مورد', 'branch' => 'فرع'];
            
            // 3. بناء جدول HTML
            echo '<table class="table table-sm table-hover table-striped mb-0">';
            echo '<thead><tr><th>الفرع</th><th>نوع الكيان</th><th>اسم الكيان</th><th class="w-1"></th></tr></thead><tbody>';
            
            if (empty($links)) {
                echo '<tr><td colspan="4" class="text-center text-muted p-3">لم يتم ربط أي كيانات.</td></tr>';
            } else {
                foreach ($links as $link) {
                    $entity_name = 'غير معروف (ID: ' . $link['entity_id'] . ')';
                    $branch_name = '<span class="text-muted">—</span>'; // قيمة افتراضية للفرع
                    $table_name = '';
                    $name_column = '';
                    
                    // 4. تحديد الجدول والعمود لجلب الاسم
                    switch ($link['entity_type']) {
                        case 'property': $table_name = 'properties'; $name_column = 'property_name'; break;
                        case 'owner':    $table_name = 'owners';     $name_column = 'owner_name'; break;
                        case 'client':   $table_name = 'clients';    $name_column = 'client_name'; break;
                        case 'supplier': $table_name = 'suppliers';  $name_column = 'supplier_name'; break;
                        case 'branch':   $table_name = 'branches';   $name_column = 'branch_name'; break;
                    }

                    // 5. جلب اسم الكيان والفرع المرتبط به
                    if ($table_name) {
                        // جلب اسم الكيان
                        $name_stmt = $pdo->prepare("SELECT {$name_column} FROM {$table_name} WHERE id = ?");
                        $name_stmt->execute([$link['entity_id']]);
                        $entity_name = $name_stmt->fetchColumn() ?: $entity_name;

                        // جلب اسم الفرع (يعمل للعقارات حاليًا، ويمكن توسيعه)
                        if ($link['entity_type'] === 'property') {
                            $branch_stmt = $pdo->prepare("SELECT b.branch_name FROM branches b JOIN properties p ON b.id = p.branch_id WHERE p.id = ?");
                            $branch_stmt->execute([$link['entity_id']]);
                            $branch_name = $branch_stmt->fetchColumn() ?: $branch_name;
                        }
                    }
                    
                    // 6. طباعة الصف في الجدول
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($branch_name) . '</td>';
                    echo '<td><span class="badge bg-secondary-lt">' . htmlspecialchars($entity_type_names[$link['entity_type']] ?? $link['entity_type']) . '</span></td>';
                    echo '<td>' . htmlspecialchars($entity_name) . '</td>';
                    echo '<td><a href="#" class="btn btn-sm btn-ghost-danger delete-link-btn" data-link-id="' . $link['id'] . '">حذف</a></td>';
                    echo '</tr>';
                }
            }
            echo '</tbody></table>';
            exit();
        }
        elseif ($page === 'documents/add_link_ajax') {
            $sql = "INSERT INTO entity_documents (document_id, entity_type, entity_id) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['doc_id'], $_POST['entity_type'], $_POST['entity_id']]);
            $response = ['success' => true];
        }
        elseif ($page === 'documents/delete_link_ajax') {
            $sql = "DELETE FROM entity_documents WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['link_id']]);
            $response = ['success' => true];
        }

        // --- Users AJAX Handlers ---
        elseif ($page === 'users/handle_add') {
            $pdo->beginTransaction();
            try {
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                $created_at = !empty($_POST['created_at']) ? $_POST['created_at'] : date('Y-m-d H:i:s');
                $sql = "INSERT INTO users (full_name, username, email, mobile, password, role_id, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['full_name'], $_POST['username'], $_POST['email'], 
                    $_POST['mobile'], $hashed_password, $_POST['role_id'],
                    $is_active, 
                    $created_at
                ]);
                $user_id = $pdo->lastInsertId();

                // حفظ الفروع المرتبطة
                if (!empty($_POST['branches'])) {
                    $branch_sql = "INSERT INTO user_branches (user_id, branch_id) VALUES (?, ?)";
                    $branch_stmt = $pdo->prepare($branch_sql);
                    foreach ($_POST['branches'] as $branch_id) {
                        $branch_stmt->execute([$user_id, $branch_id]);
                    }
                }
                $pdo->commit();
                $response = ['success' => true, 'message' => 'تم إضافة المستخدم بنجاح.'];
            } catch (Exception $e) {
                $pdo->rollBack();
                $response['message'] = $e->getMessage();
            }
        }
        elseif ($page === 'users/handle_edit') {
            $pdo->beginTransaction();
            try {
                $user_id = $_POST['id'];
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // تحديث البيانات الأساسية
                $created_at = !empty($_POST['created_at']) ? $_POST['created_at'] : date('Y-m-d H:i:s');
                $sql = "UPDATE users SET full_name=?, username=?, email=?, mobile=?, role_id=?, is_active=?, created_at=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['full_name'], $_POST['username'], $_POST['email'], 
                    $_POST['mobile'], $_POST['role_id'], $is_active,
                    $created_at, 
                    $user_id
                ]);
                
                // تحديث كلمة المرور فقط إذا لم تكن فارغة
                if (!empty($_POST['password'])) {
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $pw_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $pw_stmt->execute([$hashed_password, $user_id]);
                }

                // تحديث الفروع (حذف القديم ثم إضافة الجديد)
                $delete_stmt = $pdo->prepare("DELETE FROM user_branches WHERE user_id = ?");
                $delete_stmt->execute([$user_id]);
                if (!empty($_POST['branches'])) {
                    $branch_sql = "INSERT INTO user_branches (user_id, branch_id) VALUES (?, ?)";
                    $branch_stmt = $pdo->prepare($branch_sql);
                    foreach ($_POST['branches'] as $branch_id) {
                        $branch_stmt->execute([$user_id, $branch_id]);
                    }
                }

                $pdo->commit();
                $response = ['success' => true, 'message' => 'تم تحديث المستخدم بنجاح.'];
            } catch (Exception $e) {
                $pdo->rollBack();
                $response['message'] = $e->getMessage();
            }
        }

        elseif ($page === 'users/delete') {
            if (isset($_GET['id'])) {
                // لا تقم بحذف المستخدم رقم 1 (المدير الخارق)
                if ($_GET['id'] == 1) {
                    // يمكنك هنا إضافة رسالة خطأ إذا أردت
                } else {
                    $sql = "UPDATE users SET deleted_at = NOW() WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_GET['id']]);
                }
            }
            header("Location: index.php?page=users");
            exit();
        }

        // --- (جديد) معالجات الأرشيف ---
        elseif ($page === 'archive/restore') {
            if (isset($_GET['table']) && isset($_GET['id'])) {
                $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']); // تنظيف اسم الجدول للأمان
                $id = (int)$_GET['id'];
                $sql = "UPDATE `{$table}` SET deleted_at = NULL WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
            }
            header("Location: index.php?page=archive");
            exit();
        }
        elseif ($page === 'archive/force_delete') {
            if (isset($_GET['table']) && isset($_GET['id'])) {
                $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']);
                $id = (int)$_GET['id'];
                $sql = "DELETE FROM `{$table}` WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
            }
            header("Location: index.php?page=archive");
            exit();
        }
        // --- (جديد) معالج الإجراءات الجماعية للأرشيف ---
        elseif ($page === 'archive/batch_action') {
            if (isset($_POST['table']) && isset($_POST['action']) && isset($_POST['ids'])) {
                $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table']);
                $action = $_POST['action'];
                $ids = $_POST['ids'];

                // التأكد من أن ids هي مصفوفة من الأرقام الصحيحة للأمان
                $ids = array_map('intval', $ids);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                
                $sql = '';
                if ($action === 'restore') {
                    $sql = "UPDATE `{$table}` SET deleted_at = NULL WHERE id IN ({$placeholders})";
                } elseif ($action === 'force_delete') {
                    $sql = "DELETE FROM `{$table}` WHERE id IN ({$placeholders})";
                }

                if ($sql) {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($ids);
                }
            }
            header("Location: index.php?page=archive");
            exit();
        }
        // --- (جديد ومُصحَّح) معالجات الاستعادة والحذف الفردي ---
        elseif ($page === 'archive/restore') {
            if (isset($_GET['table']) && isset($_GET['id'])) {
                $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']);
                $id = (int)$_GET['id'];
                $sql = "UPDATE `{$table}` SET deleted_at = NULL WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
            }
            header("Location: index.php?page=archive");
            exit(); // <-- الخروج بعد التنفيذ
        }
        elseif ($page === 'archive/force_delete') {
            if (isset($_GET['table']) && isset($_GET['id'])) {
                $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']);
                $id = (int)$_GET['id'];
                $sql = "DELETE FROM `{$table}` WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
            }
            header("Location: index.php?page=archive");
            exit(); // <-- الخروج بعد التنفيذ
        }
        // --- Roles & Permissions Handlers ---
        elseif ($page === 'roles/handle_add') {
            $sql = "INSERT INTO roles (role_name, description) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$_POST['role_name'], $_POST['description']])) {
                $response = ['success' => true, 'message' => 'تم إضافة الدور بنجاح.'];
            } else {
                $response = ['success' => false, 'message' => 'فشل إضافة الدور.'];
            }

        }

        elseif ($page === 'roles/handle_edit_permissions') {
            $role_id = $_POST['role_id'];
            $permissions = $_POST['permissions'] ?? [];
            if ($role_id != 1) { // لا تسمح بتعديل المدير الخارق
                $pdo->beginTransaction();
                try {
                    $delete_stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
                    $delete_stmt->execute([$role_id]);
                    if (!empty($permissions)) {
                        $insert_sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                        $insert_stmt = $pdo->prepare($insert_sql);
                        foreach ($permissions as $permission_id) {
                            $insert_stmt->execute([$role_id, $permission_id]);
                        }
                    }
                    $pdo->commit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                }
            }
            header("Location: index.php?page=roles/edit&id=" . $role_id);
            exit();
        }
        elseif ($page === 'roles/delete') {
            $role_id = $_GET['id'] ?? 0;
            // لا تسمح بحذف أول دورين (المدير الخارق والمدير)
            if ($role_id > 2) {
                $sql = "UPDATE roles SET deleted_at = NOW() WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$role_id]);
            }
            header("Location: index.php?page=roles");
            exit();
        }

        // (جديد) معالج تعديل بيانات الدور
        elseif ($page === 'roles/handle_edit_role') {
            $sql = "UPDATE roles SET role_name = ?, description = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$_POST['role_name'], $_POST['description'], $_POST['id']])) {
                $response = ['success' => true, 'message' => 'تم تحديث الدور بنجاح.'];
            }
        }

    // --- (جديد) Permissions Handlers ---
        elseif ($page === 'permissions/handle_add_group') {
        // (مُصحَّح) إضافة group_key إلى الاستعلام
        $stmt = $pdo->prepare("INSERT INTO permission_groups (group_name, group_key, description, display_order) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['group_name'], $_POST['group_key'], $_POST['description'], $_POST['display_order'] ?? 0]);
        $response = ['success' => true, 'message' => 'تمت إضافة المجموعة.'];
    }
        elseif ($page === 'permissions/handle_edit_group') {
        // (مُصحَّح) إضافة group_key إلى الاستعلام
        $stmt = $pdo->prepare("UPDATE permission_groups SET group_name = ?, group_key = ?, description = ?, display_order = ? WHERE id = ?");
        $stmt->execute([
            $_POST['group_name'], 
            $_POST['group_key'], 
            $_POST['description'], 
            $_POST['display_order'] ?? 0, 
            $_POST['id']
        ]);
        $response = ['success' => true, 'message' => 'تم تحديث المجموعة.'];
    }
    elseif ($page === 'permissions/delete_group') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("UPDATE permission_groups SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([(int)$_GET['id']]);
        }
        header("Location: index.php?page=permissions");
        exit();
    }
    elseif ($page === 'permissions/handle_add') {
        $stmt = $pdo->prepare("INSERT INTO permissions (group_id, permission_key, description) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['group_id'], $_POST['permission_key'], $_POST['description']]);
        $response = ['success' => true, 'message' => 'تمت إضافة الصلاحية.'];
    }
    elseif ($page === 'permissions/handle_edit') {
        $stmt = $pdo->prepare("UPDATE permissions SET permission_key = ?, description = ? WHERE id = ?");
        $stmt->execute([$_POST['permission_key'], $_POST['description'], $_POST['id']]);
        $response = ['success' => true, 'message' => 'تم تحديث الصلاحية.'];
    }

        // --- (جديد ومُصحَّح) معالج حذف صلاحية واحدة ---
    elseif ($page === 'permissions/delete') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("UPDATE permissions SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([(int)$_GET['id']]);
        }
        // العودة إلى نفس المجموعة بعد الحذف
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=permissions'));
        exit();
    }
    // --- (جديد) معالج حذف مجموعة كاملة ---
    elseif ($page === 'permissions/delete_group') {
        if (isset($_GET['id'])) {
            $group_id = (int)$_GET['id'];
            $pdo->beginTransaction();
            try {
                // أرشفة كل الصلاحيات داخل المجموعة
                $stmt_perms = $pdo->prepare("UPDATE permissions SET deleted_at = NOW() WHERE group_id = ?");
                $stmt_perms->execute([$group_id]);

                // أرشفة المجموعة نفسها
                $stmt_group = $pdo->prepare("UPDATE permission_groups SET deleted_at = NOW() WHERE id = ?");
                $stmt_group->execute([$group_id]);
                
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
            }
        }
        // العودة لصفحة الصلاحيات الرئيسية
        header("Location: index.php?page=permissions");
        exit();
    }

        if (isset($response)) {
            $response['message'] = 'خطأ في قاعدة البيانات: ' . $e->getMessage();
        } else {
            die('خطأ في قاعدة البيانات: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        if (isset($response)) {
            $response['message'] = $e->getMessage();
        } else {
            die('خطأ: ' . $e->getMessage());
        }
    }

    // طباعة استجابة JSON (إذا وجدت)
    if (isset($response)) {
        echo json_encode($response);
    }
    exit();
}

// --------------------------------------------------------------------------
// 6. صفحات العرض (Views)
// --------------------------------------------------------------------------
$allowed_pages = [
    'dashboard'         => ['path' => 'dashboard/dashboard_view.php', 'title' => 'لوحة التحكم'],
    'login'             => ['path' => 'login/login_view.php', 'title' => 'تسجيل الدخول'],
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
    'documents/get_custom_fields_schema_ajax' => ['path' => ''], // معالج فقط
    'documents/get_entities_for_linking_ajax' => ['path' => ''],
    'documents/get_linked_entities_ajax'      => ['path' => ''],
    'documents/add_link_ajax'                 => ['path' => ''],
    'documents/delete_link_ajax'              => ['path' => ''],
    'users'             => ['path' => 'users/users_view.php', 'title' => 'إدارة المستخدمين'],
    'users/add'         => ['path' => 'users/add_view.php', 'title' => 'إضافة مستخدم'],
    'users/edit'        => ['path' => 'users/edit_view.php', 'title' => 'تعديل مستخدم'],
    'users/delete'      => ['path' => '', 'title' => 'حذف مستخدم'], // معالجة فقط
    'roles'             => ['path' => 'roles/roles_view.php', 'title' => 'إدارة الأدوار'],
    'roles/add'         => ['path' => 'roles/add_view.php', 'title' => 'إضافة دور'],
    'roles/edit'        => ['path' => 'roles/edit_view.php', 'title' => 'تعديل الصلاحيات'],
    'roles/edit_role'   => ['path' => 'roles/edit_role_view.php', 'title' => 'تعديل الدور'],
    'permissions'       => ['path' => 'permissions/permissions_view.php', 'title' => 'إدارة الصلاحيات'],
    'permissions/add_group'     => ['path' => 'permissions/add_group_view.php', 'title' => 'إضافة مجموعة'],
    'permissions/edit_group'    => ['path' => 'permissions/edit_group_view.php', 'title' => 'تعديل مجموعة'],
    'permissions/delete_group'  => ['path' => ''], // معالجة فقط
    'permissions/add'           => ['path' => 'permissions/add_view.php', 'title' => 'إضافة صلاحية'],
    'permissions/edit'          => ['path' => 'permissions/edit_view.php', 'title' => 'تعديل صلاحية'],
    'permissions/delete'        => ['path' => ''], // معالجة فقط
    'archive'           => ['path' => 'archive/archive_view.php', 'title' => 'الأرشيف'],
    'settings/lookups'              => ['path' => 'settings/lookups_view.php', 'title' => 'تهيئة المدخلات'],
    'settings/add_lookup_group'     => ['path' => 'settings/add_lookup_group_view.php', 'title' => 'إضافة مجموعة'],
    'settings/edit_lookup_group'    => ['path' => 'settings/edit_lookup_group_view.php', 'title' => 'تعديل مجموعة'],
    'settings/add_lookup_option'    => ['path' => 'settings/add_lookup_option_view.php', 'title' => 'إضافة خيار'],
    'settings/edit_lookup_option'   => ['path' => 'settings/edit_lookup_option_view.php', 'title' => 'تعديل خيار'],
];

// --------------------------------------------------------------------------
// 7. آلية عرض الصفحات (Views)
// --------------------------------------------------------------------------
$page_path_suffix = $allowed_pages[$page]['path'] ?? null;
$page_title = $allowed_pages[$page]['title'] ?? 'الصفحة غير موجودة';

if (in_array($page, $public_pages) && $page !== 'handle_login') {
    // صفحات عامة (تسجيل الدخول مثلاً)
    $page_path = __DIR__ . '/src/modules/' . $page_path_suffix;
    if ($page_path && file_exists($page_path)) {
        require $page_path;
    } else {
        echo "404 - Page not found.";
    }
} elseif (isset($_GET['view_only'])) {
    // عرض نافذة منبثقة فقط
    $page_path = __DIR__ . '/src/modules/' . $page_path_suffix;
    if ($page_path && file_exists($page_path)) {
        require $page_path;
    }
} else {
    // صفحات لوحة التحكم الرئيسية
    ob_start();
    $page_path = __DIR__ . '/src/modules/' . $page_path_suffix;
    if ($page_path && file_exists($page_path)) {
        require $page_path;
    } else {
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
    }
    $page_content = ob_get_clean();
    require __DIR__ . '/templates/layout.php';
}