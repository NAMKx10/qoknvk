<?php
/**
 * handlers/owners_handler.php
 * (النسخة النهائية الموحدة - إضافة وتعديل المالك والفروع في عملية واحدة)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// --- معالج إضافة المالك ---
if ($page === 'owners/handle_add') {
    $pdo->beginTransaction();
    
    // 1. إضافة بيانات المالك الأساسية
    $fields = ['owner_name', 'owner_type', 'owner_code', 'id_number', 'mobile', 'email', 'notes'];
    $params = [];
    foreach ($fields as $field) { $params[] = $_POST[$field] ?? null; }
    
    // إضافة الحالة بناءً على الزر المضغوط
    $params[] = $_POST['status'] ?? 'Active'; // تأكد من الحفظ بالقيمة الصحيحة
    
    $sql_fields = implode(', ', $fields) . ', status';
    $sql_placeholders = implode(', ', array_fill(0, count($params), '?'));
    
    $sql = "INSERT INTO owners ({$sql_fields}) VALUES ({$sql_placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $owner_id = $pdo->lastInsertId();
    
    // 2. ربط الفروع المحددة
    if (!empty($_POST['branches'])) {
        $branch_sql = "INSERT INTO owner_branches (owner_id, branch_id) VALUES (?, ?)";
        $branch_stmt = $pdo->prepare($branch_sql);
        foreach ($_POST['branches'] as $branch_id) {
            $branch_stmt->execute([$owner_id, $branch_id]);
        }
    }
    
    $pdo->commit();
    $response = ['success' => true, 'message' => 'تمت إضافة المالك بنجاح.'];
}

// --- معالج تعديل المالك ---
elseif ($page === 'owners/handle_edit') {
    $pdo->beginTransaction();
    
    $owner_id = $_POST['id'];
    
    // 1. تحديث بيانات المالك الأساسية
    $fields = ['owner_name', 'owner_type', 'owner_code', 'id_number', 'mobile', 'email', 'notes', 'status'];
    $params = [];
    foreach ($fields as $field) { $params[] = $_POST[$field] ?? null; }
    
    $update_fields_array = [];
    foreach ($fields as $field) { $update_fields_array[] = "{$field} = ?"; }
    $update_string = implode(', ', $update_fields_array);
    
    $sql = "UPDATE owners SET {$update_string} WHERE id = ?";
    $params[] = $owner_id;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // 2. تحديث الفروع المرتبطة (حذف القديم ثم إضافة الجديد)
    $branches = $_POST['branches'] ?? [];
    
    // حذف كل الروابط القديمة أولاً
    $delete_stmt = $pdo->prepare("DELETE FROM owner_branches WHERE owner_id = ?");
    $delete_stmt->execute([$owner_id]);
    
    // إضافة الروابط الجديدة المحددة
    if (!empty($branches)) {
        $insert_stmt = $pdo->prepare("INSERT INTO owner_branches (owner_id, branch_id) VALUES (?, ?)");
        foreach ($branches as $branch_id) {
            $insert_stmt->execute([$owner_id, $branch_id]);
        }
    }
    
    $pdo->commit();
    $response = ['success' => true, 'message' => 'تم تحديث المالك بنجاح.'];
}
?>