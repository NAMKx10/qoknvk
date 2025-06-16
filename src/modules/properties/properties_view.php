<?php
// PHP - جلب البيانات والفلترة
$sql_where = " WHERE p.deleted_at IS NULL "; // <-- تم تحديد p.deleted_at
$params = [];
// لاحقًا، سنضيف هنا الفلترة والبحث والترقيم

$data_sql = "SELECT p.*, b.branch_code FROM properties p LEFT JOIN branches b ON p.branch_id = b.id {$sql_where} ORDER BY p.id DESC";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$properties = $data_stmt->fetchAll();
?>

<!-- HTML - واجهة Tabler -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">إدارة العقارات</h2>
                <div class="text-muted mt-1"><?= count($properties) ?> عقار</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=properties/add&view_only=true" data-bs-title="إضافة عقار جديد">
                    <i class="ti ti-plus me-2"></i>إضافة عقار
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
                    <th>العقار / الكود</th>
                    <th>الفرع</th>
                    <th>المالك / رقم الصك</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($properties)): ?>
                    <tr><td colspan="5" class="text-center">لا توجد عقارات مضافة بعد.</td></tr>
                <?php else: foreach($properties as $property): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="avatar me-2" style="background-image: url(./assets/static/avatars/default-building.svg)"></span>
                            <div class="flex-fill">
                                <div class="font-weight-medium"><?= htmlspecialchars($property['property_name']) ?></div>
                                <div class="text-muted"><?= htmlspecialchars($property['property_code'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-secondary-lt"><?= htmlspecialchars($property['branch_code'] ?? 'غير محدد') ?></span>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($property['owner_name'] ?? '—') ?></div>
                        <div class="text-muted">صك: <?= htmlspecialchars($property['deed_number'] ?? '—') ?></div>
                    </td>
                    <td>
                        <span class="badge bg-<?= ($property['status'] === 'نشط') ? 'success' : 'danger' ?>-lt"><?= htmlspecialchars($property['status']) ?></span>
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
