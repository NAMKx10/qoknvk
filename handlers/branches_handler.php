<?php
/**
 * handlers/branches_handler.php
 * (النسخة النهائية المؤمنة)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// معالج إضافة أو تعديل فرع
if ($page === 'branches/handle_add' || $page === 'branches/handle_edit') {
    $is_add = ($page === 'branches/handle_add');
    
    // ✨ الحارس الأمني: التحقق من الصلاحية المناسبة ✨
    $permission_needed = $is_add ? 'add_branch' : 'edit_branch';
    if (!has_permission($permission_needed)) {
        $message = $is_add ? 'ليس لديك الصلاحية لإضافة فروع.' : 'ليس لديك الصلاحية لتعديل الفروع.';
        $response = ['success' => false, 'message' => $message];
        return; // الخروج من الملف إذا لم تكن هناك صلاحية
    }
    
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
        $update_fields_array = [];
        foreach ($fields as $field) {
            $update_fields_array[] = "$field = ?";
        }
        $update_fields_array[] = "status = ?";
        $update_string = implode(', ', $update_fields_array);
        
        $sql = "UPDATE branches SET {$update_string} WHERE id = ?";
        
        $params[] = $_POST['status'] ?? 'Active'; // نستخدم Active كقيمة افتراضية
        $params[] = $_POST['id'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $message = $is_add ? 'تمت إضافة الفرع بنجاح.' : 'تم تحديث الفرع بنجاح.';
    $response = ['success' => true, 'message' => $message];
}
?>