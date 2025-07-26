<?php
/**
 * handlers/suppliers_handler.php
 * 
 * المعالج الخلفي لعمليات الإضافة والتعديل في موديول الموردين.
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// --- معالج إضافة مورد جديد ---
if ($page === 'suppliers/handle_add') {
    
    // 1. الحارس الأمني: التحقق من صلاحية الإضافة
    if (!has_permission('add_supplier')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لإضافة موردين جدد.'];
        return;
    }

    try {
        $pdo->beginTransaction();

        // 2. إضافة بيانات المورد الأساسية
        $fields = ['supplier_name', 'supplier_code', 'supplier_type', 'service_type', 'registration_number', 'tax_number', 'contact_person', 'mobile', 'email', 'address', 'notes'];
        $params = [];
        foreach ($fields as $field) { $params[] = $_POST[$field] ?? null; }
        
        $sql_fields = implode(', ', $fields) . ', status';
        $sql_placeholders = implode(', ', array_fill(0, count($params) + 1, '?'));
        $params[] = 'Active';
        
        $sql = "INSERT INTO suppliers ({$sql_fields}) VALUES ({$sql_placeholders})";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $supplier_id = $pdo->lastInsertId();

        // 3. ربط الفروع المحددة
        if (!empty($_POST['branches'])) {
            $branch_sql = "INSERT INTO supplier_branches (supplier_id, branch_id) VALUES (?, ?)";
            $branch_stmt = $pdo->prepare($branch_sql);
            foreach ($_POST['branches'] as $branch_id) {
                $branch_stmt->execute([$supplier_id, $branch_id]);
            }
        }

        $pdo->commit();
        $response = ['success' => true, 'message' => 'تمت إضافة المورد بنجاح.'];

    } catch (Exception $e) {
        $pdo->rollBack();
        $response = ['success' => false, 'message' => 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage()];
    }
}

// --- معالج تعديل مورد حالي ---
elseif ($page === 'suppliers/handle_edit') {

    // 1. الحارس الأمني: التحقق من صلاحية التعديل
    if (!has_permission('edit_supplier')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لتعديل بيانات الموردين.'];
        return;
    }

    try {
        $pdo->beginTransaction();
        $supplier_id = $_POST['id'];

        // 2. تحديث بيانات المورد الأساسية
        $fields = ['supplier_name', 'supplier_code', 'supplier_type', 'service_type', 'registration_number', 'tax_number', 'contact_person', 'mobile', 'email', 'address', 'status', 'notes'];
        $params = [];
        foreach ($fields as $field) { $params[] = $_POST[$field] ?? null; }
        
        $update_fields_array = [];
        foreach ($fields as $field) { $update_fields_array[] = "{$field} = ?"; }
        $update_string = implode(', ', $update_fields_array);
        
        $sql = "UPDATE suppliers SET {$update_string} WHERE id = ?";
        $params[] = $supplier_id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // 3. تحديث الفروع المرتبطة (حذف القديم ثم إضافة الجديد)
        $branches = $_POST['branches'] ?? [];
        
        $delete_stmt = $pdo->prepare("DELETE FROM supplier_branches WHERE supplier_id = ?");
        $delete_stmt->execute([$supplier_id]);
        
        if (!empty($branches)) {
            $insert_stmt = $pdo->prepare("INSERT INTO supplier_branches (supplier_id, branch_id) VALUES (?, ?)");
            foreach ($branches as $branch_id) {
                $insert_stmt->execute([$supplier_id, $branch_id]);
            }
        }

        $pdo->commit();
        $response = ['success' => true, 'message' => 'تم تحديث بيانات المورد بنجاح.'];

    } catch (Exception $e) {
        $pdo->rollBack();
        $response = ['success' => false, 'message' => 'حدث خطأ أثناء تحديث البيانات: ' . $e->getMessage()];
    }
}
?>