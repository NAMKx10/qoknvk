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

// جلب الإحصائيات (استعلام مُحسَّن)
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM properties WHERE branch_id = $branch_id AND deleted_at IS NULL) as total_properties,
        (SELECT COUNT(*) FROM units u JOIN properties p ON u.property_id = p.id WHERE p.branch_id = $branch_id AND u.deleted_at IS NULL) as total_units,
        (SELECT COUNT(*) FROM clients c JOIN client_branches cb ON c.id = cb.client_id WHERE cb.branch_id = $branch_id AND c.deleted_at IS NULL) as total_clients,
        (SELECT COUNT(*) FROM suppliers s JOIN supplier_branches sb ON s.id = sb.supplier_id WHERE sb.branch_id = $branch_id AND s.deleted_at IS NULL) as total_suppliers,
        (SELECT COUNT(DISTINCT cr.id) FROM contracts_rental cr JOIN contract_units cu ON cr.id = cu.contract_id JOIN units u ON cu.unit_id = u.id JOIN properties p ON u.property_id = p.id WHERE p.branch_id = $branch_id AND cr.deleted_at IS NULL) as total_rental_contracts,
        (SELECT COUNT(DISTINCT cs.id) FROM contracts_supply cs WHERE cs.property_id IN (SELECT id FROM properties WHERE branch_id = $branch_id) AND cs.deleted_at IS NULL) as total_supply_contracts
")->fetch(PDO::FETCH_ASSOC);

// جلب قائمة العقارات
$properties = $pdo->query("SELECT id, property_name, property_code FROM properties WHERE branch_id = $branch_id AND deleted_at IS NULL")->fetchAll();

// (جديد) جلب خريطة الألوان للحالات
$statuses_map_stmt = $pdo->query("SELECT option_key, option_value, bg_color FROM lookup_options WHERE group_key = 'status'");
$statuses_map = [];
foreach ($statuses_map_stmt as $row) {
    $statuses_map[$row['option_key']] = ['name' => $row['option_value'], 'bg_color' => $row['bg_color']];
}
?>


<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"/>
    <title>ملف الفرع: <?= htmlspecialchars($branch['branch_name']) ?></title>
    <base href="/on/">
    <link href="assets/css/tabler.rtl.min.css" rel="stylesheet"/>
    <link href="assets/css/tabler-icons.min.css" rel="stylesheet"/>
    <style>
        body { font-family: 'Tajawal', sans-serif !important; background-color: #f8f9fa; color: #212529; font-size: 13px; line-height: 1.6; }
        .page-wrapper { background-color: #fff; max-width: 21cm; margin: 20px auto; padding: 1.5cm; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .report-header { text-align: center; margin-bottom: 2rem; }
        .report-header img { max-width: 150px; margin-bottom: 1rem; }
        .report-header h1 { font-size: 26px; font-weight: 700; color: #1e3a8a; border-bottom: 3px solid #1e3a8a; display: inline-block; padding-bottom: 10px; }
        .section-title { font-size: 18px; color: #1e3a8a; border-bottom: 2px solid #e0e0e0; padding-bottom: 8px; margin-top: 2rem; margin-bottom: 1rem; display: flex; align-items: center; font-weight: 700; }
        .section-title i { margin-left: 10px; color: #1e3a8a; }
        .report-table { width: 100%; margin-bottom: 1.5rem; }
        .report-table th, .report-table td { padding: 10px 12px; border-bottom: 1px solid #dee2e6; }
        .report-table th { background-color: #f8f9fa; font-weight: 700; text-align: right; width: 15%; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem; text-align: center; }
        .stat-card { background-color: #f8f9fa; padding: 1rem; border-radius: 5px; border: 1px solid #dee2e6; transition: all 0.2s ease-in-out; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .stat-card i { font-size: 2rem; color: #4263eb; }
        .stat-card .h1 { color: #1e3a8a; font-weight: 700; }
        .stat-card .subheader { color: #6c757d; font-size: 14px; }
        @media print { body{ background: #fff; } .no-print { display: none !important; } .page-wrapper { margin: 0; padding: 0; box-shadow: none; border-radius: 0; } }
    </style>
</head>
<body>
<div class="page-wrapper">
    
    <a href="#" class="btn btn-primary no-print" onclick="window.print(); return false;" style="position: absolute; top: 20px; left: 20px;">
        <i class="ti ti-printer me-2"></i>طباعة
    </a>

    <div class="report-header">
        <!-- يمكنك وضع شعار الشركة هنا -->
        <!-- <img src="assets/static/logo.svg" alt="Company Logo"> -->
        <h1>ملف تعريفي: <?= htmlspecialchars($branch['branch_name']) ?></h1>
    </div>

    <h2 class="section-title"><i class="ti ti-info-circle"></i>المعلومات الأساسية</h2>
    <table class="report-table">
        <tr>
            <th>اسم الفرع</th><td><?= htmlspecialchars($branch['branch_name'] ?? '—') ?></td>
            <th>كود الفرع</th><td><?= htmlspecialchars($branch['branch_code'] ?? '—') ?></td>
        </tr>
        <tr>
            <th>نوع الكيان</th><td><?= htmlspecialchars($branch['branch_type'] ?? '—') ?></td>
            <th>الحالة</th>
<td>
    <?php
        $status_key = $branch['status'];
        $status_info = $statuses_map[$status_key] ?? ['name' => $status_key, 'bg_color' => '#6c757d'];
    ?>
    <span class="badge" style="background-color: <?= htmlspecialchars($status_info['bg_color']) ?>; color: #fff;">
        <?= htmlspecialchars($status_info['name']) ?>
    </span>
</td>
        </tr>
        <tr><th>رقم السجل</th><td><?= htmlspecialchars($branch['registration_number'] ?? '—') ?></td><th>الرقم الضريبي</th><td><?= htmlspecialchars($branch['tax_number'] ?? '—') ?></td></tr>
        <tr><th>الجوال</th><td><?= htmlspecialchars($branch['phone'] ?? '—') ?></td><th>البريد الإلكتروني</th><td><?= htmlspecialchars($branch['email'] ?? '—') ?></td></tr>
        <tr><th>العنوان</th><td colspan="3"><?= htmlspecialchars($branch['address'] ?? '—') ?></td></tr>
        <tr><th>ملاحظات</th><td colspan="3"><?= htmlspecialchars($branch['notes'] ?? '—') ?></td></tr>
    </table>

    <h2 class="section-title"><i class="ti ti-chart-bar"></i>ملخص الإحصائيات</h2>
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); font-size: 12px;">
        <div class="stat-card"><i class="ti ti-building-arch"></i><div class="h2"><?= $stats['total_properties'] ?? 0 ?></div><div class="subheader">إجمالي العقارات</div></div>
        <div class="stat-card"><i class="ti ti-door"></i><div class="h2"><?= $stats['total_units'] ?? 0 ?></div><div class="subheader">إجمالي الوحدات</div></div>
        <div class="stat-card"><i class="ti ti-users"></i><div class="h2"><?= $stats['total_clients'] ?? 0 ?></div><div class="subheader">إجمالي العملاء</div></div>
        <div class="stat-card"><i class="ti ti-truck-delivery"></i><div class="h2"><?= $stats['total_suppliers'] ?? 0 ?></div><div class="subheader">إجمالي الموردين</div></div>
        <div class="stat-card"><i class="ti ti-file-text"></i><div class="h2"><?= $stats['total_rental_contracts'] ?? 0 ?></div><div class="subheader">عقود الإيجار</div></div>
        <div class="stat-card"><i class="ti ti-file-invoice"></i><div class="h2"><?= $stats['total_supply_contracts'] ?? 0 ?></div><div class="subheader">عقود التوريد</div></div>
    </div>


    <h2 class="section-title"><i class="ti ti-list-details"></i>قائمة العقارات التابعة للفرع</h2>
    <table class="report-table">
        <thead><tr><th>م</th><th>اسم العقار</th><th>كود العقار</th></tr></thead>
        <tbody>
        <?php if(empty($properties)): ?>
            <tr><td colspan="3" class="text-center text-muted py-3">لا توجد عقارات تابعة لهذا الفرع.</td></tr>
        <?php else: $i=1; foreach($properties as $prop): ?>
            <tr><td><?= $i++ ?></td><td><?= htmlspecialchars($prop['property_name']) ?></td><td><?= htmlspecialchars($prop['property_code']) ?></td></tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>