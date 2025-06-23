<?php
/**
 * handlers/permissions_handler.php
 * 
 * معالجات AJAX الخاصة بإدارة الصلاحيات والمجموعات (إضافة وتعديل)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

/**
 * إضافة مجموعة صلاحيات جديدة
 * [POST] group_name, group_key, description, display_order
 */
if ($page === 'permissions/handle_add_group') {
    $stmt = $pdo->prepare("INSERT INTO permission_groups (group_name, group_key, description, display_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['group_name'], $_POST['group_key'], $_POST['description'], $_POST['display_order'] ?? 0]);
    $response = ['success' => true, 'message' => 'تمت إضافة المجموعة بنجاح.'];
}

/**
 * تعديل مجموعة صلاحيات
 * [POST] id, group_name, group_key, description, display_order
 */
elseif ($page === 'permissions/handle_edit_group') {
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

/**
 * إضافة صلاحية جديدة لمجموعة
 * [POST] group_id, permission_key, description
 */
elseif ($page === 'permissions/handle_add') {
    $stmt = $pdo->prepare("INSERT INTO permissions (group_id, permission_key, description) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['group_id'], $_POST['permission_key'], $_POST['description']]);
    $response = ['success' => true, 'message' => 'تمت إضافة الصلاحية بنجاح.'];
}

/**
 * تعديل صلاحية
 * [POST] id, permission_key, description
 */
elseif ($page === 'permissions/handle_edit') {
    $stmt = $pdo->prepare("UPDATE permissions SET permission_key = ?, description = ? WHERE id = ?");
    $stmt->execute([$_POST['permission_key'], $_POST['description'], $_POST['id']]);
    $response = ['success' => true, 'message' => 'تم تحديث الصلاحية بنجاح.'];
}

?>