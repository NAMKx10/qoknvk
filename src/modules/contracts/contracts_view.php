<?php
// هذا الاستعلام صحيح ويقوم باستبعاد العقود المؤرشفة
$stmt = $pdo->query("SELECT cr.id, cr.contract_number, cr.start_date, cr.end_date, cr.total_amount, cr.status, c.client_name FROM contracts_rental cr JOIN clients c ON cr.client_id = c.id WHERE cr.deleted_at IS NULL ORDER BY cr.id DESC");
$contracts = $stmt->fetchAll();
$status_colors = ['نشط' => 'success', 'منتهي' => 'warning', 'ملغي' => 'danger'];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-file-signature ms-2"></i>إدارة عقود الإيجار</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=contracts/add&view_only=true" data-bs-title="إضافة عقد إيجار جديد">
            <i class="fas fa-plus-circle ms-1"></i>إضافة عقد جديد
        </button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr><th>#</th><th>رقم العقد</th><th>العميل</th><th>تاريخ البدء</th><th>تاريخ الانتهاء</th><th>المبلغ</th><th>الحالة</th><th>الإجراءات</th></tr>
        </thead>
        <tbody>
            <?php if (empty($contracts)): ?>
                <tr><td colspan="8" class="text-center">لا توجد عقود مسجلة بعد.</td></tr>
            <?php else: ?>
                <?php foreach ($contracts as $contract): ?>
                    <tr>
                        <td><?php echo $contract['id']; ?></td>
                        <td><?php echo htmlspecialchars($contract['contract_number'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($contract['client_name']); ?></td>
                        <td><?php echo $contract['start_date']; ?></td>
                        <td><?php echo $contract['end_date']; ?></td>
                        <td><?php echo number_format($contract['total_amount'], 2); ?></td>
                        <td><span class="badge bg-<?php echo $status_colors[$contract['status']] ?? 'secondary'; ?>"><?php echo htmlspecialchars($contract['status']); ?></span></td>
                        <td>
                            <a href="index.php?page=contracts/view&id=<?php echo $contract['id']; ?>" class="btn btn-sm btn-secondary" title="عرض الدفعات"><i class="fas fa-eye"></i></a>
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=contracts/edit&id=<?php echo $contract['id']; ?>&view_only=true" data-bs-title="تعديل العقد رقم: <?php echo htmlspecialchars($contract['contract_number']); ?>" title="تعديل"><i class="fas fa-edit"></i></button>
                            <a href="index.php?page=contracts/delete&id=<?php echo $contract['id']; ?>" class="btn btn-sm btn-danger" title="حذف" onclick="return confirm('سيتم نقل هذا العقد إلى الأرشيف. هل أنت متأكد؟');"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>