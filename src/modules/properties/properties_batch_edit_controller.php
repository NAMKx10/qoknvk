<?php
// src/modules/properties/properties_batch_edit_controller.php
// (النسخة الجديدة لتعمل مع جدول Excel)

// 1. التحقق من وجود المعرفات في الرابط
if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    die("لم يتم تحديد أي عقارات للتعديل.");
}
$ids_string = $_GET['ids'];
$ids_array = array_map('intval', explode(',', $ids_string));
$safe_ids = array_filter($ids_array, fn($id) => $id > 0);

if (empty($safe_ids)) { die("المعرفات المحددة غير صالحة."); }

// 2. جلب بيانات العقارات المحددة بصيغة JSON
$placeholders = implode(',', array_fill(0, count($safe_ids), '?'));
$sql = "SELECT * FROM properties WHERE id IN ({$placeholders}) ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($safe_ids);
// نرسل بيانات العقارات نفسها كـ JSON للواجهة
$properties_json = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

// 3. تجهيز الخيارات للقوائم المنسدلة بصيغة JSON
$branches_json = json_encode($pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL AND status = 'Active'")->fetchAll(PDO::FETCH_KEY_PAIR));
$property_types_json = json_encode(get_lookup_options($pdo, 'property_type'));
$ownership_types_json = json_encode(get_lookup_options($pdo, 'ownership_type'));
$statuses_json = json_encode(get_lookup_options($pdo, 'status', true));

// 4. استدعاء ملف الواجهة الجديد
require_once __DIR__ . '/properties_batch_edit_view.php';
?>