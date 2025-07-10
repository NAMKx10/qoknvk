<?php
/**
 * app/request_handler.php (النسخة النهائية النظيفة بعد استخدام الدوال المركزية)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

global $page, $pdo;

// --- القسم الأول: الموجّه الذكي لطلبات AJAX (لم يتغير) ---
$is_ajax_handler_request = ($page !== 'handle_login' && strpos($page, 'handle_') !== false) || strpos($page, '_ajax') !== false;

if ($is_ajax_handler_request) {
    // ... (هذا القسم يبقى كما هو دون تغيير) ...
    $response = ['success' => false, 'message' => "المعالج '$page' غير معرّف."];
    $handler_name = explode('/', $page)[0];
    $handler_path = __DIR__ . '/../handlers/' . $handler_name . '_handler.php';

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
        // ... (هذا القسم يبقى كما هو دون تغيير) ...
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND deleted_at IS NULL AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['full_name'];
            header("Location: index.php?page=dashboard");
            exit();
        } else {
            $_SESSION['login_error'] = "اسم المستخدم أو كلمة المرور غير صحيحة.";
            header("Location: index.php?page=login");
            exit();
        }
        break;

    case 'logout':
        // ... (هذا القسم يبقى كما هو دون تغيير) ...
        session_start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        header("Location: index.php?page=login");
        exit();
        break;


     case 'properties/delete':
        if (isset($_GET['id'])) {
            // نستدعي دالتنا المركزية الأنيقة
            soft_delete($pdo, 'properties', (int)$_GET['id']);
        }
        // نعيد المستخدم إلى صفحة العقارات التي كان فيها
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
        if (isset($_GET['id']) && $_GET['id'] > 2) { // لا نسمح بحذف الأدوار الأساسية
            soft_delete($pdo, 'roles', (int)$_GET['id']);
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=roles'));
        exit();
        break;

    case 'permissions/delete':
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

    // --- الحالات الخاصة التي تتطلب منطقًا مخصصًا (تبقى كما هي) ---
    case 'permissions/delete_group':
        // هذه حالة خاصة لأنها تحذف من جدولين بناءً على group_id وليس id.
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

        // نتأكد أن لدينا إجراء ومصفوفة معرفات غير فارغة
        if ($action && !empty($ids)) {
            // نضمن أن كل المعرفات هي أرقام صحيحة للأمان
            $safe_ids = array_map('intval', $ids);
            
            // نتحقق من نوع الإجراء ونستدعي الدالة المركزية المناسبة
            if ($action === 'soft_delete') {
                soft_delete($pdo, 'properties', $safe_ids);
            }
            // مستقبلاً، يمكن إضافة إجراءات أخرى هنا مثل:
            // elseif ($action === 'force_delete') {
            //     force_delete($pdo, 'properties', $safe_ids);
            // }
        }
        
        // في النهاية، نعيد المستخدم إلى صفحة العقارات
        header("Location: index.php?page=properties");
        exit();
        break;


}
?>