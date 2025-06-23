<?php
/**
 * handlers/branches_handler.php
 * 
 * معالجات AJAX الخاصة بإدارة الفروع (إضافة وتعديل)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

/**
 * إضافة فرع جديد أو تعديله
 * [POST] branch_name, branch_code, branch_type, ... etc
 * النتيجة: success, message
 */
if ($page === 'branches/handle_add' || $page === 'branches/handle_edit') {
    $is_add = ($page === 'branches/handle_add');
    
    // قائمة الحقول لتسهيل المعالجة
    $fields = [
        'branch_name', 'branch_code', 'branch_type', 
        'registration_number', 'tax_number', 'phone', 
        'email', 'address', 'notes'
    ];
    
    $params = [];
    foreach ($fields as $field) {
        $params[] = $_POST[$field] ?? null;
    }
    
    if ($is_add) {
        $sql_fields = implode(', ', $fields);
        $sql_placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $sql = "INSERT INTO branches ($sql_fields) VALUES ($sql_placeholders)";
    } else {
        // في حالة التعديل، نضيف حقل الحالة والمعرّف
        $update_fields_array = [];
        foreach ($fields as $field) {
            $update_fields_array[] = "$field = ?";
        }
        $update_fields_array[] = "status = ?"; // إضافة حقل الحالة
        $update_string = implode(', ', $update_fields_array);
        
        $sql = "UPDATE branches SET {$update_string} WHERE id = ?";
        
        // إضافة المتغيرات الخاصة بالتعديل إلى المصفوفة
        $params[] = $_POST['status'] ?? 'نشط';
        $params[] = $_POST['id'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $message = $is_add ? 'تمت إضافة الفرع بنجاح.' : 'تم تحديث الفرع بنجاح.';
    $response = ['success' => true, 'message' => $message];
}

?>