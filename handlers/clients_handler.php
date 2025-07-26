<?php
/**
 * handlers/clients_handler.php
 * (النسخة المحدثة مع حقل كود العميل)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// --- معالج إضافة عميل جديد ---
if ($page === 'clients/handle_add') {
    if (!has_permission('add_client')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لإضافة عملاء جدد.'];
        return;
    }
    try {
        $pdo->beginTransaction();
        // ✨ تمت إضافة client_code هنا ✨
        $fields = ['client_name', 'client_code', 'client_type', 'id_number', 'tax_number', 'mobile', 'email', 'representative_name', 'address', 'notes'];
        $params = [];
        foreach ($fields as $field) { $params[] = $_POST[$field] ?? null; }
        
        $sql_fields = implode(', ', $fields) . ', status';
        $sql_placeholders = implode(', ', array_fill(0, count($params) + 1, '?'));
        $params[] = 'Active';
        
        $sql = "INSERT INTO clients ({$sql_fields}) VALUES ({$sql_placeholders})";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $client_id = $pdo->lastInsertId();

        if (!empty($_POST['branches'])) {
            $branch_sql = "INSERT INTO client_branches (client_id, branch_id) VALUES (?, ?)";
            $branch_stmt = $pdo->prepare($branch_sql);
            foreach ($_POST['branches'] as $branch_id) {
                $branch_stmt->execute([$client_id, $branch_id]);
            }
        }
        $pdo->commit();
        $response = ['success' => true, 'message' => 'تمت إضافة العميل بنجاح.'];
    } catch (Exception $e) {
        $pdo->rollBack();
        $response = ['success' => false, 'message' => 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage()];
    }
}

// --- معالج تعديل عميل حالي ---
elseif ($page === 'clients/handle_edit') {
    if (!has_permission('edit_client')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لتعديل بيانات العملاء.'];
        return;
    }
    try {
        $pdo->beginTransaction();
        $client_id = $_POST['id'];

        // ✨ تمت إضافة client_code هنا ✨
        $fields = ['client_name', 'client_code', 'client_type', 'id_number', 'tax_number', 'mobile', 'email', 'representative_name', 'status', 'address', 'notes'];
        $params = [];
        foreach ($fields as $field) { $params[] = $_POST[$field] ?? null; }
        
        $update_fields_array = [];
        foreach ($fields as $field) { $update_fields_array[] = "{$field} = ?"; }
        $update_string = implode(', ', $update_fields_array);
        
        $sql = "UPDATE clients SET {$update_string} WHERE id = ?";
        $params[] = $client_id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $branches = $_POST['branches'] ?? [];
        $delete_stmt = $pdo->prepare("DELETE FROM client_branches WHERE client_id = ?");
        $delete_stmt->execute([$client_id]);
        
        if (!empty($branches)) {
            $insert_stmt = $pdo->prepare("INSERT INTO client_branches (client_id, branch_id) VALUES (?, ?)");
            foreach ($branches as $branch_id) {
                $insert_stmt->execute([$client_id, $branch_id]);
            }
        }
        $pdo->commit();
        $response = ['success' => true, 'message' => 'تم تحديث بيانات العميل بنجاح.'];
    } catch (Exception $e) {
        $pdo->rollBack();
        $response = ['success' => false, 'message' => 'حدث خطأ أثناء تحديث البيانات: ' . $e->getMessage()];
    }
}
?>