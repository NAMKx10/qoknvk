<?php
/**
 * handlers/contracts_handler.php
 * 
 * المعالج الخلفي لعمليات الإضافة والتعديل في موديول عقود الإيجار.
 * يتعامل مع عملية مركبة من 3 خطوات داخل transaction.
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// --- معالج إضافة عقد إيجار جديد ---
if ($page === 'contracts/handle_add') {
    
    // 1. الحارس الأمني
    if (!has_permission('add_contract')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لإضافة عقود جديدة.'];
        return;
    }

    try {
        $pdo->beginTransaction();

        // الخطوة 1: حفظ بيانات العقد الأساسية
        $fields = ['contract_number', 'client_id', 'start_date', 'end_date', 'total_amount', 'payment_cycle', 'notes'];
        $params = [];
        foreach ($fields as $field) { $params[] = $_POST[$field] ?? null; }
        
        $sql_fields = implode(', ', $fields) . ', status';
        $sql_placeholders = implode(', ', array_fill(0, count($params) + 1, '?'));
        $params[] = $_POST['status'] ?? 'Active';
        
        $sql = "INSERT INTO contracts_rental ({$sql_fields}) VALUES ({$sql_placeholders})";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $contract_id = $pdo->lastInsertId();

        // الخطوة 2: ربط الوحدات المحددة بالعقد
        if (!empty($_POST['unit_ids'])) {
            $unit_sql = "INSERT INTO contract_units (contract_id, unit_id) VALUES (?, ?)";
            $unit_stmt = $pdo->prepare($unit_sql);
            foreach ($_POST['unit_ids'] as $unit_id) {
                $unit_stmt->execute([$contract_id, $unit_id]);
            }
        }

        // الخطوة 3: توليد جدول الدفعات تلقائيًا
        generate_payment_schedule(
            $pdo, $contract_id, 'rental', 
            $_POST['start_date'], $_POST['end_date'], 
            $_POST['total_amount'], $_POST['payment_cycle']
        );

        $pdo->commit();
        $response = ['success' => true, 'message' => 'تمت إضافة العقد وتوليد الدفعات بنجاح.'];

    } catch (Exception $e) {
        $pdo->rollBack();
        $response = ['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()];
    }
}

// --- معالج تعديل عقد إيجار حالي ---
elseif ($page === 'contracts/handle_edit') {

    // 1. الحارس الأمني
    if (!has_permission('edit_contract')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لتعديل العقود.'];
        return;
    }

    try {
        $pdo->beginTransaction();
        $contract_id = $_POST['id'];

        // الخطوة 1: تحديث بيانات العقد الأساسية
        $fields = ['contract_number', 'client_id', 'start_date', 'end_date', 'total_amount', 'payment_cycle', 'status', 'notes'];
        $params = [];
        foreach ($fields as $field) { $params[] = $_POST[$field] ?? null; }
        
        $update_fields_array = [];
        foreach ($fields as $field) { $update_fields_array[] = "{$field} = ?"; }
        $update_string = implode(', ', $update_fields_array);
        
        $sql = "UPDATE contracts_rental SET {$update_string} WHERE id = ?";
        $params[] = $contract_id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // الخطوة 2: تحديث الوحدات المرتبطة (حذف القديم وإضافة الجديد)
        $pdo->prepare("DELETE FROM contract_units WHERE contract_id = ?")->execute([$contract_id]);
        if (!empty($_POST['unit_ids'])) {
            $unit_sql = "INSERT INTO contract_units (contract_id, unit_id) VALUES (?, ?)";
            $unit_stmt = $pdo->prepare($unit_sql);
            foreach ($_POST['unit_ids'] as $unit_id) {
                $unit_stmt->execute([$contract_id, $unit_id]);
            }
        }

        // الخطوة 3: إعادة توليد جدول الدفعات (حذف القديم وإنشاء الجديد)
        $pdo->prepare("DELETE FROM payment_schedules WHERE contract_id = ? AND contract_type = 'rental'")->execute([$contract_id]);
        generate_payment_schedule(
            $pdo, $contract_id, 'rental', 
            $_POST['start_date'], $_POST['end_date'], 
            $_POST['total_amount'], $_POST['payment_cycle']
        );

        $pdo->commit();
        $response = ['success' => true, 'message' => 'تم تحديث العقد والدفعات بنجاح.'];

    } catch (Exception $e) {
        $pdo->rollBack();
        $response = ['success' => false, 'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()];
    }
}
?>