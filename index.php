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

require_once __DIR__ . '/app/security.php';

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
        // --- Properties AJAX Handler ---
        // --- Owners AJAX Handler ---
        // --- Settings (Lookups) AJAX Handlers ---
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
    // --- Users AJAX Handlers ---
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

    // --- (جديد) Permissions Handlers ---
     
   
    elseif ($page === 'permissions/delete_group') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("UPDATE permission_groups SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([(int)$_GET['id']]);
        }
        header("Location: index.php?page=permissions");
        exit();
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


$allowed_pages = require __DIR__ . '/routes/web.php';

// --------------------------------------------------------------------------
// 7. آلية عرض الصفحات (Views)
// --------------------------------------------------------------------------


require_once __DIR__ . '/app/view_renderer.php';