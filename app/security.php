<?php
/**
 * app/security.php
 * 
 * جدار الحماية. مسؤول عن:
 * 1. التأكد من أن المستخدم مسجل دخوله للوصول للصفحات المحمية.
 * 2. تحميل صلاحيات المستخدم والفروع المسموح بها في الجلسة بعد تسجيل الدخول مباشرة.
 */

// هذه المتغيرات تأتي من index.php
global $page, $pdo;

// --- تعريف الصفحات العامة التي لا تتطلب تسجيل دخول ---
$public_pages = ['login', 'handle_login', 'logout'];


// --- التحقق من الجلسة ---
// إذا كان المستخدم غير مسجل ويحاول الوصول لصفحة غير عامة، أعد توجيهه لصفحة الدخول.
if (!isset($_SESSION['user_id']) && !in_array($page, $public_pages)) {
    header('Location: index.php?page=login');
    exit();
}


// --- تحميل الصلاحيات بعد تسجيل الدخول مباشرة (يتم مرة واحدة فقط) ---
// إذا كان المستخدم مسجل دخوله ولكن لم يتم تحميل صلاحياته بعد في الجلسة.
if (isset($_SESSION['user_id']) && !isset($_SESSION['user_permissions'])) {
    
    // جلب بيانات المستخدم الأساسية
    $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $current_user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($current_user) {
        // تخزين اسم المستخدم في الجلسة
        $_SESSION['username'] = $current_user['full_name'];

        // جلب قائمة الصلاحيات بناءً على دور المستخدم
        $permissions_stmt = $pdo->prepare("
            SELECT p.permission_key 
            FROM permissions p 
            JOIN role_permissions rp ON p.id = rp.permission_id 
            WHERE rp.role_id = ?
        ");
        $permissions_stmt->execute([$current_user['role_id']]);
        $_SESSION['user_permissions'] = $permissions_stmt->fetchAll(PDO::FETCH_COLUMN);

        // جلب الفروع المسموح بها للمستخدم
        $branches_stmt = $pdo->prepare("SELECT branch_id FROM user_branches WHERE user_id = ?");
        $branches_stmt->execute([$_SESSION['user_id']]);
        $user_branch_ids = $branches_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // إذا لم تكن هناك فروع مخصصة، اسمح له برؤية كل الفروع
        $_SESSION['user_branch_ids'] = empty($user_branch_ids) ? 'ALL' : $user_branch_ids;
    } else {
        // إذا لم يتم العثور على المستخدم في قاعدة البيانات (ربما تم حذفه)، دمر الجلسة
        session_destroy();
        header('Location: index.php?page=login');
        exit();
    }
}
?>