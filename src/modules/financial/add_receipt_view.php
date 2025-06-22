<?php
if (!isset($_GET['payment_id'])) { die("Payment ID is required."); }
$payment_id = $_GET['payment_id'];
$stmt = $pdo->prepare("
    SELECT ps.*, cr.contract_number, c.client_name 
    FROM payment_schedules ps
    JOIN contracts_rental cr ON ps.contract_id = cr.id
    JOIN clients c ON cr.client_id = c.id
    WHERE ps.id = ? AND ps.contract_type = 'rental'
");
$stmt->execute([$payment_id]);
$payment = $stmt->fetch();
if (!$payment) { die("الدفعة غير موجودة."); }
$remaining_amount = $payment['amount_due'] - $payment['amount_paid'];
?>

<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>

<form method="POST" action="index.php?page=financial/handle_add_receipt_ajax" class="ajax-form">
    <input type="hidden" name="payment_id" value="<?php echo $payment_id; ?>">
    <input type="hidden" name="contract_id" value="<?php echo $payment['contract_id']; ?>">
    
    <div class="alert alert-info">
        أنت على وشك تسجيل دفعة للعميل: <strong><?php echo htmlspecialchars($payment['client_name']); ?></strong><br>
        خاصة بالعقد رقم: <strong><?php echo htmlspecialchars($payment['contract_number']); ?></strong><br>
        تاريخ استحقاق الدفعة: <strong><?php echo $payment['due_date']; ?></strong><br>
        المبلغ المستحق: <strong><?php echo number_format($payment['amount_due'], 2); ?> ريال</strong> (المتبقي: <?php echo number_format($remaining_amount, 2); ?> ريال)
    </div>

    <div class="row g-3">
        <div class="col-sm-6">
            <label for="amount" class="form-label">المبلغ المدفوع</label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?php echo $remaining_amount; ?>" max="<?php echo $remaining_amount; ?>" required>
        </div>
        <div class="col-sm-6">
            <label for="transaction_date" class="form-label">تاريخ السداد</label>
            <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="col-sm-6">
            <label for="payment_method" class="form-label">طريقة الدفع</label>
            <select class="form-select" id="payment_method" name="payment_method">
                <option value="نقدي">نقدي</option>
                <option value="تحويل بنكي">تحويل بنكي</option>
                <option value="شيك">شيك</option>
                <option value="شبكة">شبكة</option>
            </select>
        </div>
        <div class="col-sm-6">
            <label for="reference_number" class="form-label">رقم المرجع (اختياري)</label>
            <input type="text" class="form-control" id="reference_number" name="reference_number">
        </div>
        <div class="col-12">
            <label for="description" class="form-label">ملاحظات</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>
    </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-success">حفظ سند القبض</button>
    </div>
</form>