<?php
// PHP - جلب البيانات (سنقوم بتطوير الفلترة لاحقًا)
$sql_where = " WHERE s.deleted_at IS NULL ";
$params = [];
// تطبيق فلتر الفروع التلقائي (سيتم تفعيله لاحقًا)

$data_sql = "
    SELECT s.*,
           (SELECT COUNT(*) FROM contracts_supply cs WHERE cs.supplier_id = s.id AND cs.deleted_at IS NULL) as contracts_count,
           (SELECT COUNT(*) FROM supplier_branches sb WHERE sb.supplier_id = s.id) as branch_count
    FROM suppliers s
    {$sql_where}
    ORDER BY s.id DESC
";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$suppliers = $data_stmt->fetchAll();
$total_records = count($suppliers);
?>

<!-- HTML - واجهة Tabler -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">إدارة الموردين</h2>
                <div class="text-muted mt-1"><?= $total_records ?> مورد</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=suppliers/add&view_only=true" data-bs-title="إضافة مورد جديد">
                    <i class="ti ti-plus me-2"></i>إضافة مورد
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
                    <th>المورد</th>
                    <th>الخدمة / السجل</th>
                    <th>الفروع</th>
                    <th>العقود</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($suppliers)): ?>
                    <tr><td colspan="6" class="text-center">لا توجد بيانات.</td></tr>
                <?php else: foreach($suppliers as $supplier): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="avatar me-2"><?= mb_substr($supplier['supplier_name'], 0, 2) ?></span>
                            <div class="flex-fill">
                                <div class="font-weight-medium"><?= htmlspecialchars($supplier['supplier_name']) ?></div>
                                <div class="text-muted"><?= htmlspecialchars($supplier['supplier_type'] ?? '—') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($supplier['service_type'] ?? '—') ?></div>
                        <div class="text-muted">سجل: <?= htmlspecialchars($supplier['registration_number'] ?? '—') ?></div>
                    </td>
                    <td>
                        <?php if ($supplier['branch_count'] > 0): ?>
                            <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=suppliers/branches_modal&id=<?= $supplier['id'] ?>&view_only=true" data-bs-title="الفروع المرتبطة">
                                <?= $supplier['branch_count'] ?> فرع/فروع
                            </button>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $supplier['contracts_count'] ?></td>
                    <td><span class="badge bg-<?= ($supplier['status'] === 'نشط') ? 'success' : 'danger' ?>-lt"><?= htmlspecialchars($supplier['status']) ?></span></td>
                    <td class="text-end">
                        <a href="#" class="btn btn-sm">تعديل</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>