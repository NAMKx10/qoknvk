<?php
/**
 * handlers/roles_handler.php
 * (النسخة النهائية المؤمنة)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// ✨ الحارس الأول: تأمين إضافة دور جديد ✨
if ($page === 'roles/handle_add') {
    if (!has_permission('add_role')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لإضافة أدوار.'];
        return;
    }

    $sql = "INSERT INTO roles (role_name, description) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['role_name'], $_POST['description']]);
    $response = ['success' => true, 'message' => 'تم إضافة الدور بنجاح.'];
}

// ✨ الحارس الثاني: تأمين تعديل بيانات الدور ✨
elseif ($page === 'roles/handle_edit_role') {
    // نفترض أن صلاحية تعديل الدور هي نفسها صلاحية تعديل الصلاحيات
    if (!has_permission('edit_role_permissions')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لتعديل الأدوار.'];
        return;
    }
    
    $sql = "UPDATE roles SET role_name = ?, description = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['role_name'], $_POST['description'], $_POST['id']]);
    $response = ['success' => true, 'message' => 'تم تحديث الدور بنجاح.'];
}

// ✨ الحارس الثالث: تأمين تعديل صلاحيات الدور ✨
elseif ($page === 'roles/handle_edit_permissions') {
    if (!has_permission('edit_role_permissions')) {
        // هذا المعالج لا يرجع JSON، لذا سنقوم بإعادة التوجيه مع رسالة خطأ
        $_SESSION['error_message'] = "ليس لديك الصلاحية لتنفيذ هذا الإجراء.";
        header("Location: index.php?page=dashboard");
        exit();
    }

    $role_id = $_POST['role_id'];
    if ($role_id != 1) { // لا تسمح بتعديل المدير الخارق
        $permissions = $_POST['permissions'] ?? [];
        
        $pdo->beginTransaction();
        
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
    }
    
    ob_end_clean();
    header("Location: index.php?page=roles/edit&id=" . $role_id);
    exit();
}
?>