<?php
/**
 * handlers/permissions_handler.php
 * (النسخة النهائية المؤمنة)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// ✨ الحارس الأول: تأمين إضافة مجموعة ✨
if ($page === 'permissions/handle_add_group') {
    if (!has_permission('add_permission_group')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لإضافة مجموعات.'];
        return;
    }

    $stmt = $pdo->prepare("INSERT INTO permission_groups (group_name, group_key, description, display_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['group_name'], $_POST['group_key'], $_POST['description'], $_POST['display_order'] ?? 0]);
    $response = ['success' => true, 'message' => 'تمت إضافة المجموعة بنجاح.'];
}

// ✨ الحارس الثاني: تأمين تعديل مجموعة ✨
elseif ($page === 'permissions/handle_edit_group') {
    if (!has_permission('edit_permission_group')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لتعديل المجموعات.'];
        return;
    }

    $stmt = $pdo->prepare("UPDATE permission_groups SET group_name = ?, group_key = ?, description = ?, display_order = ? WHERE id = ?");
    $stmt->execute([
        $_POST['group_name'], 
        $_POST['group_key'], 
        $_POST['description'], 
        $_POST['display_order'] ?? 0, 
        $_POST['id']
    ]);
    $response = ['success' => true, 'message' => 'تم تحديث المجموعة بنجاح.'];
}

// ✨ الحارس الثالث: تأمين إضافة صلاحية ✨
elseif ($page === 'permissions/handle_add') {
    if (!has_permission('add_permission')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لإضافة صلاحيات.'];
        return;
    }

    $stmt = $pdo->prepare("INSERT INTO permissions (group_id, permission_key, description) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['group_id'], $_POST['permission_key'], $_POST['description']]);
    $response = ['success' => true, 'message' => 'تمت إضافة الصلاحية بنجاح.'];
}

// ✨ الحارس الرابع: تأمين تعديل صلاحية ✨
elseif ($page === 'permissions/handle_edit') {
    if (!has_permission('edit_permission')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لتعديل الصلاحيات.'];
        return;
    }

    $stmt = $pdo->prepare("UPDATE permissions SET permission_key = ?, description = ? WHERE id = ?");
    $stmt->execute([$_POST['permission_key'], $_POST['description'], $_POST['id']]);
    $response = ['success' => true, 'message' => 'تم تحديث الصلاحية بنجاح.'];
}
?>