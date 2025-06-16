<?php
if (!isset($_GET['id'])) { header('Location: index.php?page=supply_contracts'); exit(); }
$contract_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT cs.*, s.supplier_name, p.property_name FROM contracts_supply cs JOIN suppliers s ON cs.supplier_id = s.id JOIN properties p ON cs.property_id = p.id WHERE cs.id = ?");
$stmt->execute([$contract_id]);
$contract = $stmt->fetch();
if (!$contract) { header('Location: index.php?page=supply_contracts'); exit(); }
$payments_stmt = $pdo->prepare("SELECT * FROM payment_schedules WHERE contract_type = 'supply' AND contract_id = ? ORDER BY due_date ASC");
$payments_stmt->execute([$contract_id]);
$payments = $payments_stmt->fetchAll();
$status_colors = ['مستحق' => 'primary', 'مدفوع جزئي' => 'info', 'مدفوع بالكامل' => 'success', 'متأخر' => 'danger'];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-file-invoice ms-2"></i>تفاصيل عقد التوريد للمورد: <?php echo htmlspecialchars($contract['supplier_name']); ?></h1>
    <a href="index.php?page=supply_contracts" class="btn btn-sm btn-outline-secondary">العودة</a>
</div>

<h4>جدول الدفعات</h4>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-light">
            <tr><th>#</th><th>تاريخ الاستحقاق</th><th>المبلغ المستحق</th><th>المبلغ المدفوع</th><th>المتبقي</th><th>الحالة</th><th>الإجراءات</th></tr>
        </thead>
        <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="7" class="text-center">لم يتم توليد دفعات لهذا العقد بعد.</td></tr>
            <?php else: ?>
                <?php foreach ($payments as $index => $payment): ?>
                    <?php $remaining = $payment['amount_due'] - $payment['amount_paid']; ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $payment['due_date']; ?></td>
                        <td><?php echo number_format($payment['amount_due'], 2); ?></td>
                        <td><?php echo number_format($payment['amount_paid'], 2); ?></td>
                        <td><?php echo number_format($remaining, 2); ?></td>
                        <td><span class="badge bg-<?php echo $status_colors[$payment['status']]; ?>"><?php echo $payment['status']; ?></span></td>
                        <td>
                            <?php if ($remaining > 0): ?>
                                <!-- /// تم تحويل الزر هنا /// -->
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=financial/add_payment&payment_id=<?php echo $payment['id']; ?>&view_only=true" data-bs-title="تسجيل سند صرف">
                                    تسجيل صرف
                                </button>
                            <?php else: ?>
                                <span class="text-muted">مسدد</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>