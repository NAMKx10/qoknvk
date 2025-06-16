<?php
if (!isset($_GET['payment_id'])) { die("Payment ID is required."); }
$payment_id = $_GET['payment_id'];
$stmt = $pdo->prepare("SELECT ps.*, cs.contract_number, s.supplier_name FROM payment_schedules ps JOIN contracts_supply cs ON ps.contract_id = cs.id JOIN suppliers s ON cs.supplier_id = s.id WHERE ps.id = ? AND ps.contract_type = 'supply'");
$stmt->execute([$payment_id]);
$payment = $stmt->fetch();
if (!$payment) { die("Payment not found."); }
$remaining_amount = $payment['amount_due'] - $payment['amount_paid'];
?>

<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>

<form method="POST" action="index.php?page=financial/handle_add_payment_ajax" class="ajax-form">
    <input type="hidden" name="payment_id" value="<?php echo $payment_id; ?>">
    <input type="hidden" name="contract_id" value="<?php echo $payment['contract_id']; ?>">
    
    <div class="alert alert-warning">
        أنت على وشك تسجيل دفعة للمورد: <strong><?php echo htmlspecialchars($payment['supplier_name']); ?></strong><br>
        خاصة بالعقد رقم: <strong><?php echo htmlspecialchars($payment['contract_number'] ?? 'N/A'); ?></strong><br>
        تاريخ استحقاق الدفعة: <strong><?php echo $payment['due_date']; ?></strong><br>
        المبلغ المستحق: <strong><?php echo number_format($payment['amount_due'], 2); ?> ريال</strong> (المتبقي: <?php echo number_format($remaining_amount, 2); ?> ريال)
    </div>

    <div class="row g-3">
        <div class="col-sm-6">
            <label for="amount" class="form-label">المبلغ المصروف</label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?php echo $remaining_amount; ?>" max="<?php echo $remaining_amount; ?>" required>
        </div>
        <div class="col-sm-6">
            <label for="transaction_date" class="form-label">تاريخ الصرف</label>
            <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="col-sm-6">
            <label for="payment_method" class="form-label">طريقة الصرف</label>
            <select class="form-select" id="payment_method" name="payment_method">
                <option value="تحويل بنكي">تحويل بنكي</option>
                <option value="نقدي">نقدي</option>
                <option value="شيك">شيك</option>
            </select>
        </div>
        <div class="col-sm-6">
            <label for="reference_number" class="form-label">رقم المرجع (اختياري)</label>
            <input type="text" class="form-control" id="reference_number" name="reference_number">
        </div>
        <div class="col-12">
            <label for="description" class="form-label">الوصف / البيان</label>
            <textarea class="form-control" id="description" name="description" rows="2" placeholder="اكتب هنا تفاصيل عملية الصرف..."></textarea>
        </div>
    </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-danger">حفظ سند الصرف</button>
    </div>
</form>