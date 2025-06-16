<?php
if (!isset($_GET['id'])) { die("ID is required."); }
$contract_id = $_GET['id'];
$contract_stmt = $pdo->prepare("SELECT * FROM contracts_rental WHERE id = ?");
$contract_stmt->execute([$contract_id]);
$contract = $contract_stmt->fetch();
if (!$contract) { die("Contract not found."); }
$clients_stmt = $pdo->query("SELECT id, client_name FROM clients WHERE status = 'نشط' ORDER BY client_name");
$clients_list = $clients_stmt->fetchAll();
$current_units_stmt = $pdo->prepare("SELECT unit_id FROM contract_units WHERE contract_id = ?");
$current_units_stmt->execute([$contract_id]);
$current_unit_ids = $current_units_stmt->fetchAll(PDO::FETCH_COLUMN);
$units_stmt = $pdo->query("SELECT u.id, u.unit_name, p.property_name FROM units u JOIN properties p ON u.property_id = p.id WHERE u.status = 'متاحة' OR u.id IN (" . (empty($current_unit_ids) ? '0' : implode(',', $current_unit_ids)) . ") ORDER BY p.property_name, u.unit_name");
$units_list = $units_stmt->fetchAll();
$page_scripts = <<<JS
<script>
$(document).ready(function() {
$('#mainModal .select2-init').each(function() {
$(this).select2({
theme: 'bootstrap-5',
dir: "rtl",
placeholder: $(this).data('placeholder'),
dropdownParent: $('#mainModal'),
closeOnSelect: !$(this).prop('multiple')
});
});
});
</script>
JS;
?>
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=contracts/handle_edit_ajax" class="ajax-form">
<input type="hidden" name="id" value="<?php echo $contract['id']; ?>">
<div class="row g-3">
<div class="col-12"><label for="client_id" class="form-label">العميل</label><select class="form-select select2-init" id="client_id" name="client_id" required data-placeholder="ابحث عن عميل..."><option></option><?php foreach ($clients_list as $client): ?><option value="<?php echo $client['id']; ?>" <?php echo ($contract['client_id'] == $client['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($client['client_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label for="units" class="form-label">الوحدات</label><select class="form-select select2-init" id="units" name="units[]" multiple required data-placeholder="ابحث واختر وحدة أو أكثر..."><?php foreach ($units_list as $unit): ?><option value="<?php echo $unit['id']; ?>" <?php echo in_array($unit['id'], $current_unit_ids) ? 'selected' : ''; ?>><?php echo htmlspecialchars($unit['property_name'] . ' - ' . $unit['unit_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-sm-6"><label for="contract_number" class="form-label">رقم العقد المرجعي</label><input type="text" class="form-control" id="contract_number" name="contract_number" value="<?php echo htmlspecialchars($contract['contract_number']); ?>"></div>
<div class="col-sm-6"><label for="total_amount" class="form-label">مبلغ الإيجار الإجمالي</label><input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" value="<?php echo htmlspecialchars($contract['total_amount']); ?>" required></div>
<div class="col-sm-6"><label for="start_date" class="form-label">تاريخ بداية العقد</label><input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $contract['start_date']; ?>" required></div>
<div class="col-sm-6"><label for="end_date" class="form-label">تاريخ نهاية العقد</label><input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $contract['end_date']; ?>" required></div>
<div class="col-sm-6"><label for="payment_cycle" class="form-label">دورة السداد</label><select class="form-select" id="payment_cycle" name="payment_cycle"><option value="دفعة واحدة" <?php echo ($contract['payment_cycle'] == 'دفعة واحدة') ? 'selected' : ''; ?>>دفعة واحدة</option><option value="شهري" <?php echo ($contract['payment_cycle'] == 'شهري') ? 'selected' : ''; ?>>شهري</option><option value="ربع سنوي" <?php echo ($contract['payment_cycle'] == 'ربع سنوي') ? 'selected' : ''; ?>>ربع سنوي</option><option value="نصف سنوي" <?php echo ($contract['payment_cycle'] == 'نصف سنوي') ? 'selected' : ''; ?>>نصف سنوي</option><option value="سنوي" <?php echo ($contract['payment_cycle'] == 'سنوي') ? 'selected' : ''; ?>>سنوي</option></select></div>
<div class="col-12"><label for="notes" class="form-label">ملاحظات العقد</label><textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($contract['notes']); ?></textarea></div>
</div>
<hr class="my-4">
<div class="d-flex justify-content-end">
<button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
<button type="submit" class="btn btn-primary">حفظ التعديلات</button>
</div>
</form>