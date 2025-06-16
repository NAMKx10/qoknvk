<?php
/*
 * الملف: src/modules/reports/unit_profile_view.php
 * الوظيفة: قالب مستقل وكامل لطباعة الملف الشخصي للوحدة.
*/

// --- 1. التأسيس والتحقق ---
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || !isset($pdo) || !function_exists('has_permission')) {
    require_once __DIR__ . '/../../../config/database.php'; 
    require_once __DIR__ . '/../../../src/core/functions.php';
}

if (!has_permission('view_units')) { die('Access Denied.'); } 
if (!isset($_GET['id'])) { die("Unit ID is required."); }

// --- 2. جلب البيانات ---
$unit_id = $_GET['id'];
$unit_stmt = $pdo->prepare("SELECT u.*, p.property_name, p.owner_name, p.city FROM units u JOIN properties p ON u.property_id = p.id WHERE u.id = ? AND u.deleted_at IS NULL");
$unit_stmt->execute([$unit_id]);
$unit = $unit_stmt->fetch();
if (!$unit) { die("Unit not found."); }

$contract = null;
if ($unit['status'] === 'مؤجرة') {
    $contract_stmt = $pdo->prepare("SELECT cr.contract_number, cr.start_date, cr.end_date, cr.payment_cycle, cr.total_amount, cr.notes, c.client_name FROM contracts_rental cr JOIN contract_units cu ON cr.id = cu.contract_id JOIN clients c ON cr.client_id = c.id WHERE cu.unit_id = ? AND cr.status = 'نشط' AND cr.deleted_at IS NULL ORDER BY cr.id DESC LIMIT 1");
    $contract_stmt->execute([$unit_id]);
    $contract = $contract_stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملف الوحدة: <?php echo htmlspecialchars($unit['unit_name']); ?></title>
    <!-- تحميل Bootstrap والخطوط -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        body { font-family: 'Tajawal', sans-serif; background-color: #f8f9fa; }
        .container { max-width: 960px; background-color: #fff; padding: 2rem; border-radius: 0.5rem; margin-top: 2rem; }
        .table th { background-color: #e9ecef; }
        .card-header h5 { margin-bottom: 0; }
        @media print { 
            .no-print { display: none; } 
            body { background-color: #fff; }
            .container { margin-top: 0; padding: 0; border-radius: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="container shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">ملف الوحدة</h2>
            <button onclick="window.print();" class="btn btn-primary no-print d-flex align-items-center">
                <i class="fas fa-print"></i><span class="ms-2">طباعة</span>
            </button>
        </div>
        
        <!-- بطاقة بيانات الوحدة -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">بيانات الوحدة</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><strong>اسم الوحدة:</strong> <?php echo htmlspecialchars($unit['unit_name']); ?></div>
                    <div class="col-md-4"><strong>كود الوحدة:</strong> <?php echo htmlspecialchars($unit['unit_code'] ?? '—'); ?></div>
                    <div class="col-md-4"><strong>نوع الوحدة:</strong> <?php echo htmlspecialchars($unit['unit_type']); ?></div>
                    <div class="col-md-4"><strong>الدور:</strong> <?php echo htmlspecialchars($unit['floor'] ?? '—'); ?></div>
                    <div class="col-md-4"><strong>المساحة:</strong> <?php echo number_format($unit['area'] ?? 0, 2); ?> م²</div>
                    <div class="col-md-4"><strong>الحالة:</strong> <span class="badge bg-<?php echo ($unit['status'] == 'متاحة') ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($unit['status']); ?></span></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6"><strong>العقار التابع له:</strong> <?php echo htmlspecialchars($unit['property_name']); ?></div>
                    <div class="col-md-6"><strong>المالك:</strong> <?php echo htmlspecialchars($unit['owner_name']); ?></div>
                </div>
            </div>
        </div>

        <!-- بطاقة بيانات عقد الإيجار -->
        <div class="card mb-4">
            <div class="card-header"><h5>بيانات عقد الإيجار الحالي</h5></div>
            <div class="card-body">
                <?php if ($contract): ?>
                    <table class="table table-bordered table-sm mb-0">
                        <tbody>
                            <tr>
                                <th class="table-light" style="width: 20%;">رقم العقد</th><td><?php echo htmlspecialchars($contract['contract_number']); ?></td>
                                <th class="table-light" style="width: 20%;">المستأجر</th><td><?php echo htmlspecialchars($contract['client_name']); ?></td>
                            </tr>
                            <tr>
                                <th class="table-light">تاريخ البدء</th><td><?php echo htmlspecialchars($contract['start_date']); ?></td>
                                <th class="table-light">تاريخ الانتهاء</th><td><?php echo htmlspecialchars($contract['end_date']); ?></td>
                            </tr>
                            <tr>
                                <th class="table-light">دورة السداد</th><td><?php echo htmlspecialchars($contract['payment_cycle']); ?></td>
                                <th class="table-light">قيمة الإيجار</th><td><?php echo number_format($contract['total_amount'], 2); ?> ريال</td>
                            </tr>
                            <?php if (!empty($contract['notes'])): ?>
                            <tr>
                                <th class="table-light">ملاحظات العقد</th>
                                <td colspan="3"><?php echo nl2br(htmlspecialchars($contract['notes'])); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted text-center my-3">لا يوجد عقد إيجار نشط مرتبط بهذه الوحدة حالياً.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- الأقسام المستقبلية (تبقى كما هي) -->
        <div class="card mb-4"><div class="card-header"><h5>عدادات الخدمات</h5></div><div class="card-body"><p class="text-muted text-center my-3">سيتم عرض بيانات العدادات هنا في التحديثات القادمة.</p></div></div>
        <div class="card"><div class="card-header"><h5>وحدات التكييف</h5></div><div class="card-body"><p class="text-muted text-center my-3">سيتم عرض بيانات وحدات التكييف هنا في التحديثات القادمة.</p></div></div>
    </div>
</body>
</html>