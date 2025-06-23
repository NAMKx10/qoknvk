<?php
/**
 * handlers/owners_handler.php
 * 
 * معالجات AJAX الخاصة بإدارة الملاك (إضافة، تعديل، تحديث الفروع)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

/**
 * إضافة مالك جديد أو تعديله
 * [POST] owner_name, owner_type, ... etc
 * النتيجة: success, message
 */
if ($page === 'owners/handle_add' || $page === 'owners/handle_edit') {
    $is_add = ($page === 'owners/handle_add');
    
    $pdo->beginTransaction();

    $fields = ['owner_name', 'owner_type', 'owner_code', 'id_number', 'mobile', 'email', 'notes'];
    $params = [];
    foreach ($fields as $field) {
        $params[] = $_POST[$field] ?? null;
    }
    
    if ($is_add) {
        $sql = "INSERT INTO owners (owner_name, owner_type, owner_code, id_number, mobile, email, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $owner_id = $pdo->lastInsertId();
        
        // ربط الفروع عند الإضافة
        if (!empty($_POST['branches'])) {
            $branch_sql = "INSERT INTO owner_branches (owner_id, branch_id) VALUES (?, ?)";
            $branch_stmt = $pdo->prepare($branch_sql);
            foreach ($_POST['branches'] as $branch_id) {
                $branch_stmt->execute([$owner_id, $branch_id]);
            }
        }
    } else {
        $update_fields_array = [];
        foreach ($fields as $field) {
            $update_fields_array[] = "$field = ?";
        }
        $update_fields_array[] = "status = ?";
        $update_string = implode(', ', $update_fields_array);
        
        $sql = "UPDATE owners SET {$update_string} WHERE id = ?";
        
        $params[] = $_POST['status'] ?? 'نشط';
        $params[] = $_POST['id'];
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    $pdo->commit();
    
    $message = $is_add ? 'تمت إضافة المالك بنجاح.' : 'تم تحديث المالك بنجاح.';
    $response = ['success' => true, 'message' => $message];
}

/**
 * تحديث الفروع المرتبطة بالمالك
 * [POST] owner_id, branches[]
 * النتيجة: success, message
 */
elseif ($page === 'owners/handle_update_branches') {
    $owner_id = $_POST['owner_id'];
    $branches = $_POST['branches'] ?? [];
    
    $pdo->beginTransaction();
    
    // حذف الروابط القديمة
    $delete_stmt = $pdo->prepare("DELETE FROM owner_branches WHERE owner_id = ?");
    $delete_stmt->execute([$owner_id]);
    
    // إضافة الروابط الجديدة
    if (!empty($branches)) {
        $insert_stmt = $pdo->prepare("INSERT INTO owner_branches (owner_id, branch_id) VALUES (?, ?)");
        foreach ($branches as $branch_id) {
            $insert_stmt->execute([$owner_id, $branch_id]);
        }
    }
    
    $pdo->commit();
    $response = ['success' => true, 'message' => 'تم تحديث الفروع بنجاح.'];
}

?>