<?php
// PHP - جلب البيانات
$sql_where = " WHERE cr.deleted_at IS NULL ";
$params = [];
// لاحقًا سنضيف الفلترة والبحث

$data_sql = "
    SELECT 
        cr.id, cr.contract_number, cr.start_date, cr.end_date, cr.total_amount, cr.status,
        c.client_name, c.id as client_id
    FROM contracts_rental cr 
    JOIN clients c ON cr.client_id = c.id
    {$sql_where} 
    ORDER BY cr.id DESC
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
                <h2 class="page-title">عقود الإيجار</h2>
                <div class="text-muted mt-1"><?= $total_records ?> عقد</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=contracts/add&view_only=true" data-bs-title="إضافة عقد إيجار جديد">
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
                    <th>العميل</th>
                    <th>الفترة</th>
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
                        <a href="index.php?page=contracts/view&id=<?= $contract['id'] ?>" class="text-reset" tabindex="-1">
                            <?= htmlspecialchars($contract['contract_number'] ?? 'عقد #' . $contract['id']) ?>
                        </a>
                    </td>
                    <td>
                        <a href="index.php?page=clients&q=<?= urlencode($contract['client_name']) ?>">
                            <?= htmlspecialchars($contract['client_name']) ?>
                        </a>
                    </td>
                    <td>
                        <?= date('Y/m/d', strtotime($contract['start_date'])) ?> - <?= date('Y/m/d', strtotime($contract['end_date'])) ?>
                    </td>
                    <td><?= number_format($contract['total_amount'], 2) ?></td>
                    <td><span class="badge bg-<?= ($contract['status'] === 'نشط') ? 'success' : 'danger' ?>-lt"><?= htmlspecialchars($contract['status']) ?></span></td>
                    <td class="text-end">
                        <a href="index.php?page=contracts/view&id=<?= $contract['id'] ?>" class="btn btn-sm">عرض الدفعات</a>
                        <a href="#" class="btn btn-sm">تعديل</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>