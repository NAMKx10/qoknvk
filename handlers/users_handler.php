<?php
/**
 * handlers/users_handler.php
 * 
 * معالجات AJAX الخاصة بإدارة المستخدمين (إضافة وتعديل)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

/**
 * إضافة مستخدم جديد
 * [POST] full_name, username, email, mobile, password, role_id, is_active, created_at, branches[]
 * النتيجة: success, message
 */
if ($page === 'users/handle_add') {
    $pdo->beginTransaction();
    
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
}

/**
 * تعديل بيانات مستخدم
 * [POST] id, full_name, username, email, mobile, password (اختياري), role_id, is_active, created_at, branches[]
 * النتيجة: success, message
 */
elseif ($page === 'users/handle_edit') {
    $pdo->beginTransaction();
    
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
}
?>