<?php
/**
 * app/request_handler.php (النسخة النهائية الكاملة والشاملة)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

global $page, $pdo;

// --- القسم الأول: الموجّه الذكي لطلبات AJAX (سليم ولا يحتاج تعديل) ---
$is_ajax_handler_request = strpos($page, 'handle_') !== false || strpos($page, '_ajax') !== false;

if ($is_ajax_handler_request) {
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


// --- القسم الثاني: معالجات إعادة التوجيه المباشر (باستخدام Switch للتنظيم) ---
switch ($page) {
    // --- معالجات المصادقة ---
    case 'handle_login':
        // (هذا هو كود معالج تسجيل الدخول بالكامل)
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
        // (هذا هو كود معالج تسجيل الخروج بالكامل)
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

    // --- معالجات الحذف ---
    case 'users/delete':
        if (isset($_GET['id']) && $_GET['id'] != 1) {
            $stmt = $pdo->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$_GET['id']]);
        }
        header("Location: index.php?page=users");
        exit();
        break;

    case 'documents/delete':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("UPDATE documents SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$_GET['id']]);
        }
        header("Location: index.php?page=documents");
        exit();
        break;

    case 'roles/delete':
        if (isset($_GET['id']) && $_GET['id'] > 2) {
            $stmt = $pdo->prepare("UPDATE roles SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$_GET['id']]);
        }
        header("Location: index.php?page=roles");
        exit();
        break;

    case 'permissions/delete':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("UPDATE permissions SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$_GET['id']]);
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?page=permissions'));
        exit();
        break;
        
    case 'permissions/delete_group':
        if (isset($_GET['id'])) {
            $pdo->beginTransaction();
            $stmt_perms = $pdo->prepare("UPDATE permissions SET deleted_at = NOW() WHERE group_id = ?");
            $stmt_perms->execute([(int)$_GET['id']]);
            $stmt_group = $pdo->prepare("UPDATE permission_groups SET deleted_at = NOW() WHERE id = ?");
            $stmt_group->execute([(int)$_GET['id']]);
            $pdo->commit();
        }
        header("Location: index.php?page=permissions");
        exit();
        break;

    case 'settings/delete_lookup_option':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("UPDATE lookup_options SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$_GET['id']]);
        }
        header("Location: index.php?page=settings/lookups");
        exit();
        break;

    case 'settings/delete_lookup_group':
        if (isset($_GET['group'])) {
            $stmt = $pdo->prepare("UPDATE lookup_options SET deleted_at = NOW() WHERE group_key = ?");
            $stmt->execute([$_GET['group']]);
        }
        header("Location: index.php?page=settings/lookups");
        exit();
        break;

    // --- معالجات الأرشيف ---
    case 'archive/restore':
    case 'archive/force_delete':
        if (isset($_GET['table']) && isset($_GET['id'])) {
            $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']);
            $id = (int)$_GET['id'];
            $sql = ($page === 'archive/restore') ? "UPDATE `{$table}` SET deleted_at = NULL WHERE id = ?" : "DELETE FROM `{$table}` WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
        }
        header("Location: index.php?page=archive");
        exit();
        break;

    case 'archive/batch_action':
        if (isset($_POST['table']) && isset($_POST['action']) && isset($_POST['ids'])) {
            $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table']);
            $ids = array_map('intval', $_POST['ids']);
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sql = '';
                if ($_POST['action'] === 'restore') {
                    $sql = "UPDATE `{$table}` SET deleted_at = NULL WHERE id IN ({$placeholders})";
                } elseif ($_POST['action'] === 'force_delete') {
                    $sql = "DELETE FROM `{$table}` WHERE id IN ({$placeholders})";
                }
                if ($sql) {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($ids);
                }
            }
        }
        header("Location: index.php?page=archive");
        exit();
        break;

    // --- معالجات خاصة ---
    case 'roles/handle_edit_permissions':
        $role_id = $_POST['role_id'];
        if ($role_id != 1) {
            $permissions = $_POST['permissions'] ?? [];
            $pdo->beginTransaction();
            $delete_stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $delete_stmt->execute([$role_id]);
            if (!empty($permissions)) {
                $insert_sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                $insert_stmt = $pdo->prepare($insert_sql);
                foreach ($permissions as $permission_id) { $insert_stmt->execute([$role_id, $permission_id]); }
            }
            $pdo->commit();
        }
        header("Location: index.php?page=roles/edit&id=" . $role_id);
        exit();
        break;
}

?>