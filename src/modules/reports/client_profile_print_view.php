<?php
/*
 * الملف: src/modules/reports/client_profile_print_view.php
 * الوظيفة: قالب مستقل وكامل لطباعة الملف الشخصي للعميل.
*/

// --- 1. التأسيس والتحقق من الصلاحيات ---
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || !isset($pdo) || !function_exists('has_permission')) {
    require_once __DIR__ . '/../../../config/database.php'; 
    require_once __DIR__ . '/../../../src/core/functions.php';
}

if (!has_permission('view_clients')) { die('Access Denied'); }
if (!isset($_GET['id'])) { die("Client ID is required."); }

// --- 2. جلب بيانات العميل والعقود ---
$client_id = $_GET['id'];
$client_stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ? AND deleted_at IS NULL");
$client_stmt->execute([$client_id]);
$client = $client_stmt->fetch();
if (!$client) { die("Client not found."); }

$contracts_stmt = $pdo->prepare("
    SELECT cr.contract_number, cr.start_date, cr.end_date, cr.total_amount, cr.payment_cycle, cr.notes AS contract_notes, 
           p.property_name, u.unit_name, u.unit_code, u.unit_type
    FROM contracts_rental cr
    LEFT JOIN contract_units cu ON cr.id = cu.contract_id
    LEFT JOIN units u ON cu.unit_id = u.id
    LEFT JOIN properties p ON u.property_id = p.id
    WHERE cr.client_id = ? AND cr.deleted_at IS NULL AND cr.status = 'نشط'
    ORDER BY cr.id, u.id
");
$contracts_stmt->execute([$client_id]);
$contracts = $contracts_stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملف العميل: <?php echo htmlspecialchars($client['client_name']); ?></title>
    <!-- تحميل Bootstrap والخطوط -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        body { font-family: 'Tajawal', sans-serif; background-color: #f8f9fa; }
        .container { max-width: 960px; background-color: #fff; padding: 2rem; border-radius: 0.5rem; margin-top: 2rem; }
        .table th { background-color: #e9ecef; }
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
            <h2 class="mb-0">ملف العميل</h2>
            <button onclick="window.print();" class="btn btn-primary no-print d-flex align-items-center">
                <i class="fas fa-print"></i><span>&nbsp; طباعة</span>
            </button>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">بيانات العميل: <?php echo htmlspecialchars($client['client_name']); ?></h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><strong>رقم الهوية/السجل:</strong> <?php echo htmlspecialchars($client['id_number'] ?? '—'); ?></div>
                    <div class="col-md-6"><strong>الرقم الضريبي:</strong> <?php echo htmlspecialchars($client['tax_number'] ?? '—'); ?></div>
                    <div class="col-md-6"><strong>الجوال:</strong> <?php echo htmlspecialchars($client['mobile'] ?? '—'); ?></div>
                    <div class="col-md-6"><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($client['email'] ?? '—'); ?></div>
                    <div class="col-md-6"><strong>اسم الممثل:</strong> <?php echo htmlspecialchars($client['representative_name'] ?? '—'); ?></div>
                    <div class="col-md-6"><strong>العنوان الوطني:</strong> <?php echo htmlspecialchars($client['address'] ?? '—'); ?></div>
                    <?php if(!empty($client['notes'])): ?>
                    <div class="col-12 mt-3"><strong>ملاحظات العميل:</strong> <p class="d-inline text-muted"><?php echo nl2br(htmlspecialchars($client['notes'])); ?></p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">العقود النشطة (<?php echo count($contracts); ?>)</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>رقم العقد</th><th>العقار</th><th>اسم الوحدة</th><th>قيمة العقد</th><th>دورة السداد</th><th>الفترة</th><th>ملاحظات</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contracts)): ?>
                                <tr><td colspan="7" class="text-center p-3">لا يوجد عقود نشطة حالياً لهذا العميل.</td></tr>
                            <?php else: ?>
                                <?php foreach ($contracts as $contract): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($contract['contract_number']); ?></td>
                                        <td><?php echo htmlspecialchars($contract['property_name']); ?></td>
                                        <td><?php echo htmlspecialchars($contract['unit_name']); ?></td>
                                        <td><?php echo number_format($contract['total_amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($contract['payment_cycle']); ?></td>
                                        <td><?php echo $contract['start_date'] . " إلى " . $contract['end_date']; ?></td>
                                        <td><?php echo htmlspecialchars($contract['contract_notes']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>