<?php
// src/modules/reports/branch_profile_print_view.php
// (النسخة النهائية لطباعة ملف الفرع - مبنية على النموذج القياسي)

// --- التأكد من وجود المتغيرات الأساسية وجلب البيانات ---
if (!isset($pdo)) { require_once __DIR__ . '/../../../config/database.php'; }
if (!isset($_GET['id'])) { die("Branch ID is required."); }
$branch_id = (int)$_GET['id'];

// جلب بيانات الفرع
$branch = $pdo->query("SELECT * FROM branches WHERE id = $branch_id")->fetch();
if(!$branch) die("الفرع غير موجود.");

// جلب إحصائيات الفرع (استعلام مجمع)

$stats = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM properties WHERE branch_id = $branch_id AND deleted_at IS NULL) as total_properties,
        (SELECT COUNT(*) FROM units u JOIN properties p ON u.property_id = p.id WHERE p.branch_id = $branch_id AND u.deleted_at IS NULL) as total_units,
        (SELECT COUNT(*) FROM clients c JOIN client_branches cb ON c.id = cb.client_id WHERE cb.branch_id = $branch_id AND c.deleted_at IS NULL) as total_clients,
        (SELECT COUNT(*) FROM suppliers s JOIN supplier_branches sb ON s.id = sb.supplier_id WHERE sb.branch_id = $branch_id AND s.deleted_at IS NULL) as total_suppliers,
        (SELECT COUNT(DISTINCT cr.id) FROM contracts_rental cr JOIN contract_units cu ON cr.id = cu.contract_id JOIN units u ON cu.unit_id = u.id JOIN properties p ON u.property_id = p.id WHERE p.branch_id = $branch_id AND cr.deleted_at IS NULL) as total_rental_contracts,
        (SELECT COUNT(DISTINCT cs.id) FROM contracts_supply cs WHERE cs.property_id IN (SELECT id FROM properties WHERE branch_id = $branch_id) AND cs.deleted_at IS NULL) as total_supply_contracts
")->fetch(PDO::FETCH_ASSOC);

$properties = $pdo->query("SELECT id, property_name, property_code FROM properties WHERE branch_id = $branch_id AND deleted_at IS NULL")->fetchAll();

?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"/>
    <title>ملف الفرع: <?= htmlspecialchars($branch['branch_name']) ?></title>
    <!-- توحيد المسارات والأنماط -->
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
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="border:none;">ملف الفرع: <?= htmlspecialchars($branch['branch_name']) ?></h1>
        <a href="#" class="btn btn-primary no-print" onclick="window.print(); return false;">طباعة</a>
    </div>

    <h2>معلومات الفرع الرئيسية</h2>
<table class="report-table">
    <tr>
        <th>اسم الفرع</th><td><?= htmlspecialchars($branch['branch_name'] ?? '—') ?></td>
        <th>كود الفرع</th><td colspan="3"><?= htmlspecialchars($branch['branch_code'] ?? '—') ?></td>
    </tr>
    <tr>
        <th>نوع الكيان</th><td><?= htmlspecialchars($branch['branch_type'] ?? '—') ?></td>
        <th>الحالة</th><td><span class="badge bg-<?= ($branch['status'] === 'نشط') ? 'success' : 'danger' ?>-lt"><?= htmlspecialchars($branch['status']) ?></span></td>
        <th>تاريخ الإنشاء</th><td><?= date('Y-m-d', strtotime($branch['created_at'])) ?></td>
    </tr>
    <tr>
        <th>رقم السجل</th><td><?= htmlspecialchars($branch['registration_number'] ?? '—') ?></td>
        <th>الرقم الضريبي</th><td colspan="3"><?= htmlspecialchars($branch['tax_number'] ?? '—') ?></td>
    </tr>
     <tr>
        <th>الجوال</th><td><?= htmlspecialchars($branch['phone'] ?? '—') ?></td>
        <th>البريد الإلكتروني</th><td colspan="3"><?= htmlspecialchars($branch['email'] ?? '—') ?></td>
    </tr>
    <tr>
        <th>العنوان</th><td colspan="5"><?= htmlspecialchars($branch['address'] ?? '—') ?></td>
    </tr>
    <tr>
        <th>الملاحظات</th><td colspan="5"><?= htmlspecialchars($branch['notes'] ?? '—') ?></td>
    </tr>
</table>

    <h2>ملخص الإحصائيات</h2>
<div class="row g-2 text-center">
    <div class="col">
        <div class="border p-2">
            <div class="h6 m-0">إجمالي العقارات</div>
            <div class="h3 m-0"><?= $stats['total_properties'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col">
        <div class="border p-2">
            <div class="h6 m-0">إجمالي الوحدات</div>
            <div class="h3 m-0"><?= $stats['total_units'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col">
        <div class="border p-2">
            <div class="h6 m-0">إجمالي العملاء</div>
            <div class="h3 m-0"><?= $stats['total_clients'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col">
        <div class="border p-2">
            <div class="h6 m-0">إجمالي الموردين</div>
            <div class="h3 m-0"><?= $stats['total_suppliers'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col">
        <div class="border p-2">
            <div class="h6 m-0">عقود الإيجار</div>
            <div class="h3 m-0"><?= $stats['total_rental_contracts'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col">
        <div class="border p-2">
            <div class="h6 m-0">عقود التوريد</div>
            <div class="h3 m-0"><?= $stats['total_supply_contracts'] ?? 0 ?></div>
        </div>
    </div>
</div>

    <h2>قائمة العقارات التابعة للفرع</h2>
    <table class="report-table">
        <thead><tr><th>م</th><th>اسم العقار</th><th>كود العقار</th></tr></thead>
        <tbody>
            <?php if(empty($properties)): ?>
                <tr><td colspan="3" class="text-center text-muted py-3">لا توجد عقارات تابعة لهذا الفرع.</td></tr>
            <?php else: $i=1; foreach($properties as $prop): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($prop['property_name']) ?></td>
                    <td><?= htmlspecialchars($prop['property_code']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <!-- بقية الجداول المستقبلية (مثل العملاء والموردين) يمكن إضافتها هنا بنفس النمط -->

</div>
</body>
</html>