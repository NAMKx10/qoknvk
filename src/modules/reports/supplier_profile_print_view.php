<?php
/*
 * الملف: src/modules/reports/supplier_profile_print_view.php
 * الوظيفة: قالب مستقل وكامل لطباعة الملف الشخصي للمورد.
*/

// --- 1. التأسيس والتحقق ---
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || !isset($pdo) || !function_exists('has_permission')) {
    require_once __DIR__ . '/../../../config/database.php'; 
    require_once __DIR__ . '/../../../src/core/functions.php';
}

if (!has_permission('view_suppliers')) { die('Access Denied'); } 
if (!isset($_GET['id'])) { die("Supplier ID is required."); }

// --- 2. جلب بيانات المورد والعقود ---
$supplier_id = $_GET['id'];
$supplier_stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ? AND deleted_at IS NULL");
$supplier_stmt->execute([$supplier_id]);
$supplier = $supplier_stmt->fetch();
if (!$supplier) { die("Supplier not found."); }

$contracts_stmt = $pdo->prepare("
    SELECT cs.contract_number, cs.start_date, cs.end_date, cs.total_amount, p.property_name, cs.service_description
    FROM contracts_supply cs
    LEFT JOIN properties p ON cs.property_id = p.id
    WHERE cs.supplier_id = ? AND cs.deleted_at IS NULL AND cs.status = 'نشط'
    ORDER BY cs.id DESC
");
$contracts_stmt->execute([$supplier_id]);
$contracts = $contracts_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملف المورد: <?php echo htmlspecialchars($supplier['supplier_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>@import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');body{font-family:'Tajawal',sans-serif;background-color:#f8f9fa;}.container{max-width:960px;background-color:#fff;padding:2rem;border-radius:0.5rem;margin-top:2rem;}.table th{background-color:#e9ecef;}@media print{.no-print{display:none;}body{background-color:#fff;}.container{margin-top:0;padding:0;border-radius:0;box-shadow:none;}}</style>
</head>
<body>
    <div class="container shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4"><h2 class="mb-0">ملف المورد</h2><button onclick="window.print();" class="btn btn-primary no-print d-flex align-items-center"><i class="fas fa-print"></i><span class="ms-2">طباعة</span></button></div>
        <div class="card mb-4"><div class="card-header"><h5 class="mb-0">بيانات المورد: <?php echo htmlspecialchars($supplier['supplier_name']); ?></h5></div><div class="card-body"><div class="row g-3"><div class="col-md-6"><strong>رقم السجل:</strong> <?php echo htmlspecialchars($supplier['registration_number'] ?? '—'); ?></div><div class="col-md-6"><strong>الرقم الضريبي:</strong> <?php echo htmlspecialchars($supplier['tax_number'] ?? '—'); ?></div><div class="col-md-6"><strong>الجوال:</strong> <?php echo htmlspecialchars($supplier['mobile'] ?? '—'); ?></div><div class="col-md-6"><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($supplier['email'] ?? '—'); ?></div><div class="col-md-6"><strong>مسؤول التواصل:</strong> <?php echo htmlspecialchars($supplier['contact_person'] ?? '—'); ?></div><div class="col-md-6"><strong>العنوان:</strong> <?php echo htmlspecialchars($supplier['address'] ?? '—'); ?></div></div></div></div>
        <div class="card"><div class="card-header"><h5 class="mb-0">عقود التوريد النشطة (<?php echo count($contracts); ?>)</h5></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered table-sm mb-0"><thead class="table-light"><tr><th>رقم العقد</th><th>العقار</th><th>الخدمة</th><th>قيمة العقد</th><th>الفترة</th></tr></thead><tbody><?php if(empty($contracts)): ?><tr><td colspan="5" class="text-center p-3">لا توجد عقود نشطة حالياً لهذا المورد.</td></tr><?php else: foreach($contracts as $contract): ?><tr><td><?php echo htmlspecialchars($contract['contract_number']); ?></td><td><?php echo htmlspecialchars($contract['property_name']); ?></td><td><?php echo htmlspecialchars($contract['service_description']); ?></td><td><?php echo number_format($contract['total_amount'],2); ?></td><td><?php echo $contract['start_date']." إلى ".$contract['end_date']; ?></td></tr><?php endforeach; endif; ?></tbody></table></div></div></div>
    </div>
</body>
</html>