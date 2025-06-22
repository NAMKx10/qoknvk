<?php
// PHP - جلب البيانات
$sql_where = " WHERE cs.deleted_at IS NULL ";
$params = [];
// لاحقًا سنضيف الفلترة والبحث

$data_sql = "
    SELECT 
        cs.id, cs.contract_number, cs.start_date, cs.end_date, cs.total_amount, cs.status,
        s.supplier_name,
        p.property_name
    FROM contracts_supply cs 
    JOIN suppliers s ON cs.supplier_id = s.id
    JOIN properties p ON cs.property_id = p.id
    {$sql_where} 
    ORDER BY cs.id DESC
";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$contracts = $data_stmt->fetchAll();
$total_records = count($contracts);
?>

<!-- HTML - واجهة Tabler -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">عقود التوريد</h2>
                <div class="text-muted mt-1"><?= $total_records ?> عقد</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=supply_contracts/add&view_only=true" data-bs-title="إضافة عقد توريد جديد">
                    <i class="ti ti-plus me-2"></i>إضافة عقد
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
                    <th>رقم العقد</th>
                    <th>المورد</th>
                    <th>العقار</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($contracts)): ?>
                    <tr><td colspan="6" class="text-center">لا توجد عقود.</td></tr>
                <?php else: foreach($contracts as $contract): ?>
                <tr>
                    <td>
                        <a href="index.php?page=supply_contracts/view&id=<?= $contract['id'] ?>" class="text-reset">
                            <?= htmlspecialchars($contract['contract_number'] ?? 'عقد #' . $contract['id']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($contract['supplier_name']) ?></td>
                    <td><?= htmlspecialchars($contract['property_name']) ?></td>
                    <td><?= number_format($contract['total_amount'], 2) ?></td>
                    <td><span class="badge bg-<?= ($contract['status'] === 'نشط') ? 'success' : 'danger' ?>-lt"><?= htmlspecialchars($contract['status']) ?></span></td>
                    <td class="text-end">
                        <a href="index.php?page=supply_contracts/view&id=<?= $contract['id'] ?>" class="btn btn-sm">عرض الدفعات</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>