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

?>