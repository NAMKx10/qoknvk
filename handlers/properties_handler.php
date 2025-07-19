<?php
/**
 * handlers/properties_handler.php
 * (النسخة النهائية المؤمنة بالكامل)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// --- معالج الإضافة والتعديل الفردي ---
if ($page === 'properties/handle_add' || $page === 'properties/handle_edit') {
    
    $is_add = ($page === 'properties/handle_add');
    $permission_needed = $is_add ? 'add_property' : 'edit_property';

    // ✨ الحارس الأمني رقم 1: التحقق من صلاحية الإضافة أو التعديل ✨
    if (!has_permission($permission_needed)) {
        $message = $is_add ? 'ليس لديك الصلاحية لإضافة عقارات.' : 'ليس لديك الصلاحية لتعديل العقارات.';
        $response = ['success' => false, 'message' => $message];
        return; // الخروج من الملف إذا لم تكن هناك صلاحية
    }
    
    $data = [
        'branch_id'      => $_POST['branch_id'] ?? null,
        'property_name'  => $_POST['property_name'] ?? null,
        'property_code'  => $_POST['property_code'] ?? null,
        'property_type'  => $_POST['property_type'] ?? null,
        'ownership_type' => $_POST['ownership_type'] ?? null,
        'property_value' => empty($_POST['property_value']) ? null : $_POST['property_value'],
        'district'       => $_POST['district'] ?? null,
        'city'           => $_POST['city'] ?? null,
        'area'           => empty($_POST['area']) ? null : $_POST['area'],
        'notes'          => $_POST['notes'] ?? null,
        'status'         => $_POST['status'] ?? 'Active',
    ];

    $id = !$is_add ? ($_POST['id'] ?? null) : null;
    
    $result = save_record($pdo, 'properties', $data, $id);
    
    if ($result !== false) {
        $message = $id ? 'تم تحديث العقار بنجاح.' : 'تمت إضافة العقار بنجاح.';
        $response = ['success' => true, 'message' => $message];
    } else {
        $response = ['success' => false, 'message' => 'حدث خطأ أثناء حفظ البيانات.'];
    }
}

// --- معالج الإضافة الجماعية من جدول Excel ---
elseif ($page === 'properties/handle_batch_add') {
    // ✨ الحارس الأمني رقم 2: التحقق من صلاحية الإضافة الجماعية ✨
    if (!has_permission('batch_add_properties')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لتنفيذ هذا الإجراء.'];
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $properties_data = $input['property'] ?? [];
    $saved_count = 0;
    
    if (!empty($properties_data)) {
        $pdo->beginTransaction();
        try {
            foreach ($properties_data as $data) {
                if (!empty(trim($data['property_name'] ?? '')) && !empty($data['branch_id'])) {
                    save_record($pdo, 'properties', $data);
                    $saved_count++;
                }
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $response = ['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات. ' . $e->getMessage()];
            return;
        }
    }
    
    $response = ['success' => true, 'message' => "تم حفظ عدد {$saved_count} من السجلات بنجاح!"];
}

// --- معالج التعديل الجماعي من جدول Excel ---
elseif ($page === 'properties/handle_batch_edit') {
    // ✨ الحارس الأمني رقم 3: التحقق من صلاحية التعديل الجماعي ✨
    if (!has_permission('batch_edit_properties')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لتنفيذ هذا الإجراء.'];
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $properties_data = $input['property'] ?? [];

    $updated_count = 0;
    if (!empty($properties_data)) {
        $pdo->beginTransaction();
        try {
            foreach ($properties_data as $data) {
                if (!empty($data['id'])) {
                    $id = (int)$data['id'];
                    // ملاحظة: نزيل حقل 'id' من البيانات قبل تمريرها لدالة الحفظ
                    unset($data['id']);
                    save_record($pdo, 'properties', $data, $id);
                    $updated_count++;
                }
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $response = ['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات.'];
            return;
        }
    }
    
    $response = ['success' => true, 'message' => "تم تحديث عدد {$updated_count} من السجلات بنجاح!"];
}
?>