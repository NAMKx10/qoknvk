<?php

/**
 * handlers/properties_handler.php
 * (النسخة الجديدة المبسطة للإصدار 3.0)
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// --- معالج الإضافة والتعديل الفردي (تم تبسيطه) ---
if ($page === 'properties/handle_add' || $page === 'properties/handle_edit') {
    
    // 1. نقوم بتجميع الحقول الموجودة فقط في النموذج المبسط
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

    $id = ($page === 'properties/handle_edit') ? ($_POST['id'] ?? null) : null;
    
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
    // لأن البيانات مرسلة كـ JSON من JavaScript، نقرأها بهذه الطريقة
    $input = json_decode(file_get_contents('php://input'), true);
    $properties_data = $input['property'] ?? [];
    $saved_count = 0;
    
    if (!empty($properties_data)) {
        $pdo->beginTransaction();
        try {
            foreach ($properties_data as $data) {
                // نتجاهل الصفوف الفارغة ونحفظ فقط إذا كان اسم العقار والفرع موجودين
                if (!empty(trim($data['property_name'])) && !empty($data['branch_id'])) {
                    // نستخدم دالة الحفظ المركزية القوية
                    save_record($pdo, 'properties', $data);
                    $saved_count++;
                }
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            // في حالة حدوث خطأ، نرجع استجابة JSON بالخطأ
            $response = ['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات أثناء الحفظ. ' . $e->getMessage()];
            // ✨ الخروج المبكر مهم هنا لمنع أي مخرجات أخرى
            header('Content-Type: application/json'); echo json_encode($response); exit();
        }
    }
    
    // في حالة النجاح، نرجع استجابة JSON بالنجاح
    $response = ['success' => true, 'message' => "تم حفظ عدد {$saved_count} من السجلات بنجاح!"];
}

elseif ($page === 'properties/handle_batch_edit') {
    
    // ✨ التغيير هنا: نقرأ البيانات كـ JSON بدلاً من POST العادي ✨
    $input = json_decode(file_get_contents('php://input'), true);
    $properties_data = $input['property'] ?? [];

    $updated_count = 0;
    if (!empty($properties_data)) {
        $pdo->beginTransaction();
        try {
            // نمر على كل عقار تم إرساله (البيانات الآن لا تحتوي على ID في المفتاح)
            foreach ($properties_data as $data) {
                // نتأكد أن لدينا ID صالح قبل التحديث
                if (!empty($data['id'])) {
                    $id = (int)$data['id'];
                    // نستخدم دالتنا المركزية والموثوقة للتحديث
                    save_record($pdo, 'properties', $data, $id);
                    $updated_count++;
                }
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $response = ['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات.'];
            // نرسل استجابة الخطأ ونخرج
            header('Content-Type: application/json'); echo json_encode($response); exit();
        }
    }
    
    // نرسل استجابة النجاح
    $response = ['success' => true, 'message' => "تم تحديث عدد {$updated_count} من السجلات بنجاح!"];
}

// --- إذا كان هناك أي معالجة أخرى، يمكن إضافتها هنا ---

?>

