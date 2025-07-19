<?php
/**
 * handlers/units_handler.php
 * (المعالج الجديد والمؤمن لموديول الوحدات)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// معالج الإضافة
if ($page === 'units/handle_add') {
    if (!has_permission('add_unit')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لإضافة وحدات.'];
        return;
    }

    $data = [
        'property_id' => $_POST['property_id'],
        'unit_name'   => $_POST['unit_name'],
        'unit_code'   => $_POST['unit_code'] ?? null,
        'unit_type'   => $_POST['unit_type'] ?? null,
        'area'        => $_POST['area'] ?? null,
        'floor'       => $_POST['floor'] ?? null,
        'status'      => $_POST['status'] ?? 'Available',
        'notes'       => $_POST['notes'] ?? null,
    ];

    $result = save_record($pdo, 'units', $data);

    if ($result) {
        $response = ['success' => true, 'message' => 'تمت إضافة الوحدة بنجاح.'];
    } else {
        $response = ['success' => false, 'message' => 'حدث خطأ أثناء الحفظ.'];
    }
}

// معالج التعديل
elseif ($page === 'units/handle_edit') {
    if (!has_permission('edit_unit')) {
        $response = ['success' => false, 'message' => 'ليس لديك الصلاحية لتعديل الوحدات.'];
        return;
    }
    
    $unit_id = $_POST['id'];
    $data = [
        'property_id' => $_POST['property_id'],
        'unit_name'   => $_POST['unit_name'],
        'unit_code'   => $_POST['unit_code'] ?? null,
        'unit_type'   => $_POST['unit_type'] ?? null,
        'area'        => $_POST['area'] ?? null,
        'floor'       => $_POST['floor'] ?? null,
        'status'      => $_POST['status'] ?? 'Available',
        'notes'       => $_POST['notes'] ?? null,
    ];

    $result = save_record($pdo, 'units', $data, $unit_id);

    if ($result) {
        $response = ['success' => true, 'message' => 'تم تحديث الوحدة بنجاح.'];
    } else {
        $response = ['success' => false, 'message' => 'حدث خطأ أثناء التحديث.'];
    }
}
?>