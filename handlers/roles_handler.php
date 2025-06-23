<?php
/**
 * handlers/roles_handler.php
 * 
 * معالجات AJAX الخاصة بإدارة الأدوار (إضافة وتعديل بيانات الدور)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

/**
 * إضافة دور جديد
 * [POST] role_name, description
 * النتيجة: success, message
 */
if ($page === 'roles/handle_add') {
    $sql = "INSERT INTO roles (role_name, description) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['role_name'], $_POST['description']]);
    $response = ['success' => true, 'message' => 'تم إضافة الدور بنجاح.'];
}

/**
 * تعديل بيانات الدور (وليس الصلاحيات)
 * [POST] id, role_name, description
 * النتيجة: success, message
 */
elseif ($page === 'roles/handle_edit_role') {
    $sql = "UPDATE roles SET role_name = ?, description = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['role_name'], $_POST['description'], $_POST['id']]);
    $response = ['success' => true, 'message' => 'تم تحديث الدور بنجاح.'];
}



/**
 * تعديل صلاحيات الدور
 * [POST] role_id, permissions[]
 * ملاحظة: هذا المعالج لا يرجع JSON، بل يقوم بإعادة التوجيه.
 *       لذلك سيقوم بالخروج مباشرة.
 */
elseif ($page === 'roles/handle_edit_permissions') {
    $role_id = $_POST['role_id'];
    if ($role_id != 1) { // لا تسمح بتعديل المدير الخارق
        $permissions = $_POST['permissions'] ?? [];
        
        $pdo->beginTransaction();
        
        // حذف كل الصلاحيات القديمة
        $delete_stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $delete_stmt->execute([$role_id]);
        
        // إضافة الصلاحيات الجديدة
        if (!empty($permissions)) {
            $insert_sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
            $insert_stmt = $pdo->prepare($insert_sql);
            foreach ($permissions as $permission_id) {
                $insert_stmt->execute([$role_id, $permission_id]);
            }
        }
        $pdo->commit();
    }
    
    // ملاحظة مهمة: هذا هو الاستثناء الوحيد في ملفات الـ handlers
    // لأنه لا يرجع JSON.
    ob_end_clean(); // نظف المخرجات قبل إعادة التوجيه
    header("Location: index.php?page=roles/edit&id=" . $role_id);
    exit();
}

?>