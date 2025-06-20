<?php
// PHP - Final & Complete Version
// (All PHP data fetching code remains the same as the previous correct version)
if (!isset($pdo)) { require_once __DIR__ . '/../../../config/database.php'; require_once __DIR__ . '/../../../src/core/functions.php'; }
if (!isset($_GET['id'])) { die("ID is required."); }
$property_id = (int)$_GET['id'];
$property = $pdo->query("SELECT p.*, b.branch_name FROM properties p LEFT JOIN branches b ON p.branch_id = b.id WHERE p.id = $property_id")->fetch();
if(!$property) die("العقار غير موجود.");
$stats = $pdo->query("SELECT (SELECT COUNT(*) FROM units WHERE property_id = $property_id AND deleted_at IS NULL) as units_total, (SELECT COUNT(*) FROM units WHERE property_id = $property_id AND deleted_at IS NULL AND status = 'مؤجرة') as units_rented, (SELECT SUM(area) FROM units WHERE property_id = $property_id AND deleted_at IS NULL) as area_total, (SELECT SUM(area) FROM units WHERE property_id = $property_id AND deleted_at IS NULL AND status = 'مؤجرة') as area_rented, (SELECT COUNT(DISTINCT cr.id) FROM contracts_rental cr JOIN contract_units cu ON cr.id = cu.contract_id WHERE cu.unit_id IN (SELECT id FROM units WHERE property_id = $property_id)) as rent_contracts_count, (SELECT COUNT(DISTINCT c.id) FROM clients c JOIN contracts_rental cr ON c.id = cr.client_id JOIN contract_units cu ON cr.id = cu.contract_id WHERE cu.unit_id IN (SELECT id FROM units WHERE property_id = $property_id)) as rent_clients_count, (SELECT SUM(cr.total_amount) FROM contracts_rental cr JOIN contract_units cu ON cr.id = cu.contract_id WHERE cu.unit_id IN (SELECT id FROM units WHERE property_id = $property_id)) as rent_contracts_value, (SELECT COUNT(DISTINCT cs.id) FROM contracts_supply cs WHERE cs.property_id = $property_id) as supply_contracts_count, (SELECT COUNT(DISTINCT s.id) FROM suppliers s JOIN contracts_supply cs ON s.id = cs.supplier_id WHERE cs.property_id = $property_id) as supply_suppliers_count, (SELECT SUM(cs.total_amount) FROM contracts_supply cs WHERE cs.property_id = $property_id) as supply_contracts_value")->fetch(PDO::FETCH_ASSOC);
$stats['units_available'] = ($stats['units_total'] ?? 0) - ($stats['units_rented'] ?? 0);
$stats['area_available'] = ($stats['area_total'] ?? 0) - ($stats['area_rented'] ?? 0);
$units = $pdo->query("SELECT * FROM units WHERE property_id = $property_id AND deleted_at IS NULL")->fetchAll();
$rental_contracts = $pdo->query("SELECT cr.*, c.client_name, GROUP_CONCAT(u.unit_name SEPARATOR ', ') as unit_names FROM contracts_rental cr JOIN clients c ON cr.client_id = c.id JOIN contract_units cu ON cr.id = cu.contract_id JOIN units u ON cu.unit_id = u.id WHERE u.property_id = $property_id GROUP BY cr.id")->fetchAll();
$supply_contracts = $pdo->query("SELECT cs.*, s.supplier_name FROM contracts_supply cs JOIN suppliers s ON cs.supplier_id = s.id WHERE cs.property_id = $property_id")->fetchAll();
// Placeholder data...
$owners = [['name' => 'شركة الاستثمار العقاري', 'reg_no' => '1010001234', 'notes' => 'المالك الرئيسي']];
$deeds = [['deed_no' => '458219', 'date' => '2020-05-10', 'plot_no' => 'أ/77', 'plan_no' => '345/ج/1420', 'notes' => 'صك أساسي']];
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"/>
    <title>ملف العقار: <?= htmlspecialchars($property['property_name']) ?></title>
    <base href="/on/">
    <link href="assets/css/tabler.rtl.min.css" rel="stylesheet"/>
    <style>
        body { font-family: 'Tajawal', sans-serif !important; font-weight: 700; background-color: #fff; color: #000; font-size: 13px; line-height: 1.6; }
        .container { width: 100%; max-width: 21cm; margin: 20px auto; padding: 1cm 1cm; }
        h1, h2 { font-weight: 700; color: #000; }
        h1 { font-size: 24px; text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 1.5rem; }
        h2 { font-size: 16px; background-color: #f2f2f2; padding: 8px; margin-top: 1.5rem; margin-bottom: 0; border: 1px solid #ccc; font-weight: bold;}
        .report-table { border-collapse: collapse; width: 100%; margin-bottom: 1rem; }
        .report-table th, .report-table td { border: 1px solid #ccc; padding: 6px 8px; text-align: right; vertical-align: middle; }
        .report-table thead th { font-weight: bold; background-color: #e9ecef; }
        @media print { .no-print { display: none; } .container { margin: 0; padding: 0; box-shadow: none; max-width: 100%; } }
    </style>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;"><h1 style="border:none;">ملف العقار: <?= htmlspecialchars($property['property_name']) ?></h1><a href="#" class="btn btn-primary no-print" onclick="window.print(); return false;">طباعة</a></div>

    <h2>معلومات العقار</h2>
    <table class="report-table">
        <tr><th>الفرع</th><td><?= htmlspecialchars($property['branch_name'] ?? '—') ?></td><th>كود العقار</th><td><?= htmlspecialchars($property['property_code'] ?? '—') ?></td><th>نوع التملك</th><td><?= htmlspecialchars($property['ownership_type'] ?? '—') ?></td></tr>
        <tr><th>المدينة</th><td><?= htmlspecialchars($property['city'] ?? '—') ?></td><th>الحي</th><td><?= htmlspecialchars($property['district'] ?? '—') ?></td><th>المساحة</th><td><?= number_format($property['area'] ?? 0, 2) ?> م²</td></tr>
        <tr><th>الملاحظات</th><td colspan="5"><?= htmlspecialchars($property['notes'] ?? '—') ?></td></tr>
    </table>

    <h2>ملخص الإحصائيات</h2>
    <table class="report-table">
        <tr><th>الوحدات</th><td><strong>العدد:</strong> <?= $stats['units_total'] ?> (مؤجر: <?= $stats['units_rented'] ?>, شاغر: <?= $stats['units_available'] ?>) | <strong>المساحة:</strong> <?= number_format($stats['area_total']??0)?> م² (مؤجر: <?= number_format($stats['area_rented']??0)?> م²)</td></tr>
        <tr><th>عقود الإيجار</th><td><?= $stats['rent_contracts_count'] ?> عقد (<?= $stats['rent_clients_count'] ?> عميل) بقيمة <?= number_format($stats['rent_contracts_value'] ?? 0, 2) ?> ريال</td></tr>
        <tr><th>عقود التوريد</th><td><?= $stats['supply_contracts_count'] ?> عقد (<?= $stats['supply_suppliers_count'] ?> مورد) بقيمة <?= number_format($stats['supply_contracts_value'] ?? 0, 2) ?> ريال</td></tr>
    </table>

    <h2>بطاقة الملاك</h2>
    <table class="report-table"><thead><tr><th>اسم المالك</th><th>رقم السجل</th><th>ملاحظات</th></tr></thead><tbody><tr><td colspan="3" class="text-center">سيتم بناء هذا القسم لاحقًا</td></tr></tbody></table>

    <h2>بطاقة معلومات الصك</h2>
    <table class="report-table"><thead><tr><th>رقم الصك</th><th>التاريخ</th><th>القطعة</th><th>المخطط</th><th>ملاحظات</th></tr></thead><tbody><tr><td colspan="5" class="text-center">سيتم بناء هذا القسم لاحقًا</td></tr></tbody></table>

    <h2>معلومات عقود الإيجار</h2>
    <table class="report-table"><thead><tr><th>رقم العقد</th><th>العميل</th><th>الوحدة</th><th>الفترة</th><th>القيمة</th></tr></thead><tbody><?php if(empty($rental_contracts)): ?><tr><td colspan="5" class="text-center text-muted py-3">لا توجد عقود إيجار.</td></tr><?php else: foreach($rental_contracts as $c): ?><tr><td><?=htmlspecialchars($c['contract_number'])?></td><td><?=htmlspecialchars($c['client_name'])?></td><td><?=htmlspecialchars($c['unit_names'])?></td><td><?=$c['start_date']?> إلى <?=$c['end_date']?></td><td><?=number_format($c['total_amount'])?></td></tr><?php endforeach; endif; ?></tbody></table>

    <!-- بقية الجداول المستقبلية يمكن إضافتها هنا بنفس النمط -->

</div>
</body>
</html>