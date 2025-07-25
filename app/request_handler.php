<?php
/**
 * app/request_handler.php (النسخة النهائية والمطورة)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }
global $page, $pdo;

// الطريقة القياسية والآمنة للتحقق مما إذا كان الطلب هو AJAX
$is_ajax_request = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

// --- القسم الأول: معالجة طلبات AJAX فقط ---
if ($is_ajax_request) {
    
    $response = ['success' => false, 'message' => "المعالج '$page' غير معرّف."];
    $handler_name = explode('/', $page)[0];
    $handler_path = ROOT_PATH . '/handlers/' . $handler_name . '_handler.php';

    ob_start();
    try {
        if (file_exists($handler_path)) {
            require $handler_path;
        }
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
        $response = ['success' => false, 'message' => 'خطأ في قاعدة البيانات.'];
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => $e->getMessage()];
    }
    ob_end_clean();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit();
}

// --- القسم الثاني: معالجات إعادة التوجيه (أصبحت الآن أنظف بكثير) ---
switch ($page) {
    // --- معالجات المصادقة (لم تتغير) ---

    
    case 'handle_login':
        $username = $_POST['username'] ?? ''; $password = $_POST['password'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND deleted_at IS NULL AND status = 'Active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['full_name'];
            header("Location: index.php?page=dashboard"); exit();
        } else {
            $_SESSION['login_error'] = "اسم المستخدم أو كلمة المرور غير صحيحة.";
            header("Location: index.php?page=login"); exit();
        }
        break;

        case 'logout':
        session_start(); $_SESSION = [];
        if (ini_get("session.use_cookies")) { $params = session_get_cookie_params(); setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]); }
        session_destroy();
        header("Location: index.php?page=login"); exit();
        break;

    case 'branches/delete':
        if (isset($_GET['id'])) {
            soft_delete($pdo, 'branches', (int)$_GET['id']);
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=branches'));
        exit();
        break;

        
    case 'units/delete':
        if (!has_permission('delete_unit')) {
            $_SESSION['error_message'] = "ليس لديك الصلاحية لتنفيذ هذا الإجراء.";
            header("Location: index.php?page=dashboard");
            exit();
        }
        if (isset($_GET['id'])) {
            soft_delete($pdo, 'units', (int)$_GET['id']);
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=units'));
        exit();
        break;


        case 'properties/delete':
        if (!has_permission('delete_property')) {
            $_SESSION['error_message'] = "ليس لديك الصلاحية لتنفيذ هذا الإجراء.";
            header("Location: index.php?page=dashboard");
            exit();
        }
        if (isset($_GET['id'])) {
            soft_delete($pdo, 'properties', (int)$_GET['id']);
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=properties'));
        exit();
        break;


    // --- معالجات الحذف الناعم (أصبحت تستدعي دالة موحدة) ---
    
    case 'users/delete':
        // ✨ الحارس الثالث: تأمين عملية الحذف ✨
        if (!has_permission('delete_user')) {
            // إذا حاول الوصول للرابط مباشرة، أعده للصفحة الرئيسية مع رسالة خطأ
            $_SESSION['error_message'] = "ليس لديك الصلاحية لتنفيذ هذا الإجراء.";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (isset($_GET['id']) && $_GET['id'] != 1) { // لا نسمح بحذف المدير الخارق
            soft_delete($pdo, 'users', (int)$_GET['id']);
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=users'));
        exit();
        break;

    case 'contracts/delete': 
        if (isset($_GET['id'])) { 
            soft_delete($pdo, 'contracts_rental', (int)$_GET['id']);
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=contracts'));
        exit();
        break;

    case 'documents/delete':
        if (isset($_GET['id'])) {
            soft_delete($pdo, 'documents', (int)$_GET['id']);
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=documents'));
        exit();
        break;

    case 'roles/delete':
        // ✨ الحارس الرابع: تأمين عملية الحذف ✨
        if (!has_permission('delete_role')) {
            $_SESSION['error_message'] = "ليس لديك الصلاحية لتنفيذ هذا الإجراء.";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (isset($_GET['id']) && $_GET['id'] > 2) { // لا نسمح بحذف الأدوار الأساسية
            soft_delete($pdo, 'roles', (int)$_GET['id']);
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=roles'));
        exit();
        break;

    case 'permissions/delete':
        if (!has_permission('delete_permission')) {
            $_SESSION['error_message'] = "ليس لديك الصلاحية لتنفيذ هذا الإجراء.";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (isset($_GET['id'])) {
            soft_delete($pdo, 'permissions', (int)$_GET['id']);
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=permissions'));
        exit();
        break;
        
    case 'settings/delete_lookup_option':
        if (isset($_GET['id'])) {
            soft_delete($pdo, 'lookup_options', (int)$_GET['id']);
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=settings/lookups'));
        exit();
        break;

    case 'permissions/delete_group':
        if (!has_permission('delete_permission_group')) {
            $_SESSION['error_message'] = "ليس لديك الصلاحية لتنفيذ هذا الإجراء.";
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (isset($_GET['id'])) {
            $pdo->beginTransaction();
            soft_delete($pdo, 'permissions', $pdo->query("SELECT id FROM permissions WHERE group_id = " . (int)$_GET['id'])->fetchAll(PDO::FETCH_COLUMN));
            soft_delete($pdo, 'permission_groups', (int)$_GET['id']);
            $pdo->commit();
        }
        header("Location: index.php?page=permissions");
        exit();
        break;

    case 'settings/delete_lookup_group':
        // حالة خاصة أخرى لأنها تحذف بناءً على مفتاح نصي (group_key).
        if (isset($_GET['group'])) {
            $stmt = $pdo->prepare("UPDATE lookup_options SET deleted_at = NOW() WHERE group_key = ?");
            $stmt->execute([$_GET['group']]);
        }
        header("Location: index.php?page=settings/lookups");
        exit();
        break;

    // --- معالجات الأرشيف (الآن تستدعي الدوال المركزية) ---
    case 'archive/restore':
    
    case 'archive/force_delete':
        if (isset($_GET['table']) && isset($_GET['id'])) {
            if ($page === 'archive/restore') {
                restore_from_archive($pdo, $_GET['table'], (int)$_GET['id']);
            } else {
                force_delete($pdo, $_GET['table'], (int)$_GET['id']);
            }
        }
        header("Location: index.php?page=archive");
        exit();
        break;

    case 'archive/batch_action':
        if (isset($_POST['table']) && isset($_POST['action']) && isset($_POST['ids'])) {
            $table = $_POST['table'];
            $ids = array_map('intval', $_POST['ids']);
            
            if ($_POST['action'] === 'restore') {
                restore_from_archive($pdo, $table, $ids);
            } elseif ($_POST['action'] === 'force_delete') {
                force_delete($pdo, $table, $ids);
            }
        }
        header("Location: index.php?page=archive");
        exit();
        break;

        
     case 'properties/batch_action':
        $action = $_POST['action'] ?? null;
        $ids = $_POST['row_id'] ?? [];

        if ($action === 'soft_delete' && !has_permission('batch_delete_properties')) {
            $_SESSION['error_message'] = "ليس لديك الصلاحية لتنفيذ هذا الإجراء.";
            header("Location: index.php?page=properties");
            exit();
        }

        if ($action && !empty($ids)) {
            $safe_ids = array_map('intval', $ids);
            if ($action === 'soft_delete') {
                soft_delete($pdo, 'properties', $safe_ids);
            }
        }
        
        header("Location: index.php?page=properties");
        exit();
        break;

    case 'settings/handle_update':
        if (!has_permission('manage_settings')) {
            $_SESSION['error_message'] = "ليس لديك الصلاحية لتنفيذ هذا الإجراء.";
            header("Location: index.php?page=dashboard"); exit();
        }
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $toggle_keys = ['enable_delete_confirmation'];
            foreach ($toggle_keys as $key) { if (!isset($_POST[$key])) { $_POST[$key] = '0'; } }
            foreach ($_POST as $key => $value) { $stmt->execute([$value, $key]); }
            $pdo->commit();
            $_SESSION['success_message'] = "تم حفظ الإعدادات بنجاح.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = "حدث خطأ أثناء حفظ الإعدادات.";
        }
        header("Location: index.php?page=settings");
        exit();
        break;
}

// إذا لم يتطابق مع أي حالة، أعده للصفحة الرئيسية
header("Location: index.php?page=dashboard");
exit();

?>