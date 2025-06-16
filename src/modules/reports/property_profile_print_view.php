<?php
// --- 1. التأسيس والتحقق من الصلاحيات ---
// (هذا الجزء لم يعد ضرورياً لأن print.php يقوم به، لكن نبقيه كحماية إضافية)
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || !isset($pdo) || !function_exists('has_permission')) {
    // إذا تم الوصول للملف مباشرة، قم بتحميل البيئة
    require_once __DIR__ . '/../../../config/database.php'; 
    require_once __DIR__ . '/../../../src/core/functions.php';
}

if (!has_permission('view_properties')) { die('Access Denied.'); }
if (!isset($_GET['id'])) { die("Property ID is required."); }

// --- 2. جلب بيانات العقار والوحدات ---
$property_id = $_GET['id'];
$property_stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL");
$property_stmt->execute([$property_id]);
$property = $property_stmt->fetch();
if (!$property) { die("Property not found."); }

$units_stmt = $pdo->prepare("SELECT * FROM units WHERE property_id = ? AND deleted_at IS NULL ORDER BY floor, unit_name");
$units_stmt->execute([$property_id]);
$units = $units_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملف العقار: <?php echo htmlspecialchars($property['property_name']); ?></title>
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
            <h2 class="mb-0">ملف العقار</h2>
            <button onclick="window.print();" class="btn btn-primary no-print d-flex align-items-center">
                <i class="fas fa-print"></i><span>&nbsp; طباعة</span>
            </button>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">بيانات العقار: <?php echo htmlspecialchars($property['property_name']); ?></h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><strong>الكود:</strong> <?php echo htmlspecialchars($property['property_code'] ?? '—'); ?></div>
                    <div class="col-md-4"><strong>المالك:</strong> <?php echo htmlspecialchars($property['owner_name'] ?? '—'); ?></div>
                    <div class="col-md-4"><strong>رقم الصك:</strong> <?php echo htmlspecialchars($property['deed_number'] ?? '—'); ?></div>
                    <div class="col-md-4"><strong>المدينة:</strong> <?php echo htmlspecialchars($property['city'] ?? '—'); ?></div>
                    <div class="col-md-4"><strong>الحي:</strong> <?php echo htmlspecialchars($property['district'] ?? '—'); ?></div>
                    <div class="col-md-4"><strong>المساحة:</strong> <?php echo number_format($property['area'] ?? 0, 2); ?> م²</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">الوحدات التابعة للعقار (<?php echo count($units); ?>)</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>م</th><th>كود الوحدة</th><th>اسم الوحدة</th><th>نوع الوحدة</th><th>الدور</th><th>المساحة</th><th>الحالة</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($units)): ?>
                                <tr><td colspan="7" class="text-center p-3">لا توجد وحدات مضافة لهذا العقار.</td></tr>
                            <?php else: ?>
                                <?php foreach ($units as $index => $unit): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($unit['unit_code']); ?></td>
                                        <td><?php echo htmlspecialchars($unit['unit_name']); ?></td>
                                        <td><?php echo htmlspecialchars($unit['unit_type']); ?></td>
                                        <td><?php echo htmlspecialchars($unit['floor']); ?></td>
                                        <td><?php echo number_format($unit['area'], 2); ?> م²</td>
                                        <td><span class="badge bg-<?php echo ($unit['status'] == 'متاحة') ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($unit['status']); ?></span></td>
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