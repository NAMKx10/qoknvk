<?php
/**
 * handlers/users_handler.php
 * (النسخة النهائية المؤمنة والمحدثة مع حقل الحالة)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// معالج إضافة مستخدم
if ($page === 'users/handle_add') {
    if (!has_permission('add_user')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لإضافة مستخدمين.'];
        return;
    }

    $pdo->beginTransaction();
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $created_at = !empty($_POST['created_at']) ? $_POST['created_at'] : date('Y-m-d H:i:s');
    
    // ✨ التعديل هنا: استخدام `status` بدلاً من `is_active` ✨
    $sql = "INSERT INTO users (full_name, username, email, mobile, password, role_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['full_name'], $_POST['username'], $_POST['email'],
        $_POST['mobile'], $hashed_password, $_POST['role_id'],
        $_POST['status'], // ✨ الحقل الجديد
        $created_at
    ]);
    
    $user_id = $pdo->lastInsertId();
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

// معالج تعديل مستخدم
elseif ($page === 'users/handle_edit') {
    if (!has_permission('edit_user')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لتعديل المستخدمين.'];
        return;
    }
    
    $pdo->beginTransaction();
    $user_id = $_POST['id'];
    $created_at = !empty($_POST['created_at']) ? $_POST['created_at'] : date('Y-m-d H:i:s');
    
    // ✨ التعديل هنا: استخدام `status` بدلاً من `is_active` ✨
    $sql = "UPDATE users SET full_name=?, username=?, email=?, mobile=?, role_id=?, status=?, created_at=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['full_name'], $_POST['username'], $_POST['email'],
        $_POST['mobile'], $_POST['role_id'], $_POST['status'], // ✨ الحقل الجديد
        $created_at,
        $user_id
    ]);
    
    if (!empty($_POST['password'])) {
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pw_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $pw_stmt->execute([$hashed_password, $user_id]);
    }
    
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