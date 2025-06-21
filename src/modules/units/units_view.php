<?php
// PHP - جلب البيانات (سنقوم بتطوير الفلترة لاحقًا)
$sql_where = " WHERE u.deleted_at IS NULL ";
$params = [];

// تطبيق فلتر الفروع التلقائي
$sql_where .= build_branches_query_condition('p', $params);

$data_sql = "
    SELECT u.*, p.property_name 
    FROM units u 
    JOIN properties p ON u.property_id = p.id 
    {$sql_where} 
    ORDER BY u.id DESC
";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$units = $data_stmt->fetchAll();
?>

<!-- HTML - واجهة Tabler -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">إدارة الوحدات</h2>
                <div class="text-muted mt-1"><?= count($units) ?> وحدة</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=units/add&view_only=true" data-bs-title="إضافة وحدة جديدة">
                    <i class="ti ti-plus me-2"></i>إضافة وحدة
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap">
            <thead>
                <tr>
                    <th>الوحدة / الكود</th>
                    <th>العقار التابع له</th>
                    <th>النوع / المساحة</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($units)): ?>
                    <tr><td colspan="5" class="text-center">لا توجد وحدات.</td></tr>
                <?php else: foreach($units as $unit): ?>
                <tr>
                    <td>
                        <div><?= htmlspecialchars($unit['unit_name']) ?></div>
                        <div class="text-muted"><?= htmlspecialchars($unit['unit_code'] ?? 'N/A') ?></div>
                    </td>
                    <td>
                        <a href="index.php?page=properties&q=<?= urlencode($unit['property_name']) ?>"><?= htmlspecialchars($unit['property_name']) ?></a>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($unit['unit_type']) ?></div>
                        <div class="text-muted"><?= htmlspecialchars($unit['area']) ?> م²</div>
                    </td>
                    <td>
                        <span class="badge bg-<?= ($unit['status'] === 'متاحة') ? 'success' : 'warning' ?>-lt"><?= htmlspecialchars($unit['status']) ?></span>
                    </td>
                    <td class="text-end">
                        <a href="#" class="btn btn-sm">تعديل</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>