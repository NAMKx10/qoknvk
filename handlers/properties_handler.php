<?php
/**
 * handlers/properties_handler.php
 * 
 * معالجات AJAX الخاصة بإدارة العقارات (إضافة وتعديل)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

/**
 * إضافة عقار جديد أو تعديله
 * [POST] property_name, branch_id, ... etc
 * النتيجة: success, message
 */
if ($page === 'properties/handle_add' || $page === 'properties/handle_edit') {
    $is_add = ($page === 'properties/handle_add');

    $fields = [
        'branch_id', 'property_name', 'property_code', 'property_type', 
        'ownership_type', 'owner_name', 'deed_number', 'property_value', 
        'district', 'city', 'area', 'notes'
    ];
    
    $params = [];
    foreach ($fields as $field) {
        $params[] = $_POST[$field] ?? null;
    }

    if ($is_add) {
        // إضافة حقل الحالة للبيانات الجديدة
        $fields_with_status = array_merge($fields, ['status']);
        $sql_fields = implode(', ', $fields_with_status);
        $sql_placeholders = implode(', ', array_fill(0, count($fields_with_status), '?'));
        
        $params[] = $_POST['status'] ?? 'نشط'; // إضافة قيمة الحالة
        
        $sql = "INSERT INTO properties ($sql_fields) VALUES ($sql_placeholders)";
        
    } else {
        // في حالة التعديل، قم ببناء جملة الـ UPDATE
        $update_fields_array = [];
        foreach ($fields as $field) {
            $update_fields_array[] = "$field = ?";
        }
        $update_fields_array[] = "status = ?"; // إضافة حقل الحالة
        $update_string = implode(', ', $update_fields_array);

        $sql = "UPDATE properties SET {$update_string} WHERE id = ?";

        // إضافة قيمة الحالة والمعرف لمتغيرات التعديل
        $params[] = $_POST['status'] ?? 'نشط';
        $params[] = $_POST['id'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $message = $is_add ? 'تمت إضافة العقار بنجاح.' : 'تم تحديث العقار بنجاح.';
    $response = ['success' => true, 'message' => $message];
}

?>