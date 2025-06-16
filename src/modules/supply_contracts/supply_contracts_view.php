<?php
// تم إضافة WHERE cs.deleted_at IS NULL هنا
$stmt = $pdo->query("
    SELECT 
        cs.id, cs.contract_number, cs.start_date, cs.end_date, cs.total_amount, cs.status,
        s.supplier_name, p.property_name 
    FROM contracts_supply cs 
    JOIN suppliers s ON cs.supplier_id = s.id 
    JOIN properties p ON cs.property_id = p.id 
    WHERE cs.deleted_at IS NULL 
    ORDER BY cs.id DESC
");
$contracts = $stmt->fetchAll();
$status_colors = ['نشط' => 'success', 'منتهي' => 'warning', 'ملغي' => 'danger'];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-file-invoice ms-2"></i>إدارة عقود التوريد</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=supply_contracts/add&view_only=true" data-bs-title="إضافة عقد توريد جديد">
            <i class="fas fa-plus-circle ms-1"></i>إضافة عقد توريد
        </button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr><th>#</th><th>رقم العقد</th><th>اسم المورد</th><th>العقار</th><th>تاريخ البدء</th><th>تاريخ الانتهاء</th><th>المبلغ</th><th>الحالة</th><th>الإجراءات</th></tr>
        </thead>
        <tbody>
            <?php if (empty($contracts)): ?>
                <tr><td colspan="9" class="text-center">لا توجد عقود توريد مسجلة.</td></tr>
            <?php else: ?>
                <?php foreach ($contracts as $contract): ?>
                    <tr>
                        <td><?php echo $contract['id']; ?></td>
                        <td><?php echo htmlspecialchars($contract['contract_number'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($contract['supplier_name']); ?></td>
                        <td><?php echo htmlspecialchars($contract['property_name']); ?></td>
                        <td><?php echo $contract['start_date']; ?></td>
                        <td><?php echo $contract['end_date']; ?></td>
                        <td><?php echo number_format($contract['total_amount'], 2); ?></td>
                        <td><span class="badge bg-<?php echo $status_colors[$contract['status']] ?? 'secondary'; ?>"><?php echo htmlspecialchars($contract['status']); ?></span></td>
                        <td>
                            <a href="index.php?page=supply_contracts/view&id=<?php echo $contract['id']; ?>" class="btn btn-sm btn-secondary" title="عرض الدفعات"><i class="fas fa-eye"></i></a>
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=supply_contracts/edit&id=<?php echo $contract['id']; ?>&view_only=true" data-bs-title="تعديل عقد التوريد" title="تعديل"><i class="fas fa-edit"></i></button>
                            <a href="index.php?page=supply_contracts/delete&id=<?php echo $contract['id']; ?>" class="btn btn-sm btn-danger" title="حذف" onclick="return confirm('سيتم نقل هذا العقد إلى الأرشيف. هل أنت متأكد؟');"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>