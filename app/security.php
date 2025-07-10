<?php
/**
 * app/security.php
 * جدار الحماية (النسخة النهائية)
 */

global $page, $pdo;

$public_pages = ['login', 'handle_login', 'logout'];

if (!isset($_SESSION['user_id']) && !in_array($page, $public_pages)) {
    header('Location: index.php?page=login');
    exit();
}

if (isset($_SESSION['user_id']) && !isset($_SESSION['user_permissions'])) {
    
    // جلب بيانات المستخدم ودوره في استعلام واحد
    $stmt = $pdo->prepare("
        SELECT u.*, r.role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($current_user) {
        // ✨ تخزين اسم الدور في الجلسة ✨
        $_SESSION['username'] = $current_user['full_name'];
        $_SESSION['user_role_name'] = $current_user['role_name'];

        // جلب قائمة الصلاحيات
        $permissions_stmt = $pdo->prepare("
            SELECT p.permission_key 
            FROM permissions p 
            JOIN role_permissions rp ON p.id = rp.permission_id 
            WHERE rp.role_id = ?
        ");
        $permissions_stmt->execute([$current_user['role_id']]);
        $_SESSION['user_permissions'] = $permissions_stmt->fetchAll(PDO::FETCH_COLUMN);

        // جلب الفروع المسموح بها
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
?>