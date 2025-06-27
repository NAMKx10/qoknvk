<?php
// src/modules/properties/properties_batch_add_controller.php
// (النسخة المحدثة لتعمل مع جدول Excel)

// 1. تحديد عدد الصفوف الفارغة المبدئية في الجدول
$num_rows = 15; // يمكنك تعديل هذا الرقم كما تشاء

// 2. ✨ تجهيز البيانات اللازمة للقوائم المنسدلة بصيغة JSON ✨
$branches_json = json_encode($pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL AND status = 'Active'")->fetchAll(PDO::FETCH_KEY_PAIR));
$property_types_json = json_encode(get_lookup_options($pdo, 'property_type'));
$ownership_types_json = json_encode(get_lookup_options($pdo, 'ownership_type'));
$statuses_json = json_encode(get_lookup_options($pdo, 'status', true)); // نرسل المفتاح الإنجليزي والاسم العربي

// 3. استدعاء ملف الواجهة (الذي سنقوم بتحديثه لاحقًا ليحتوي على كود Excel)
require_once __DIR__ . '/properties_batch_add_view.php';
?>