<?php
if (!isset($_GET['id'])) { die("ID is required."); }
$contract_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM contracts_supply WHERE id = ?");
$stmt->execute([$contract_id]);
$contract = $stmt->fetch();
if (!$contract) { die("Contract not found."); }
$suppliers_stmt = $pdo->query("SELECT id, supplier_name FROM suppliers WHERE status = 'نشط' ORDER BY supplier_name");
$suppliers_list = $suppliers_stmt->fetchAll();
$properties_stmt = $pdo->query("SELECT id, property_name FROM properties WHERE status = 'نشط' ORDER BY property_name");
$properties_list = $properties_stmt->fetchAll();
$page_scripts = <<<JS
<script>
$(document).ready(function() {
$('#mainModal .select2-init').each(function() {
$(this).select2({
theme: 'bootstrap-5',
dir: "rtl",
placeholder: $(this).data('placeholder'),
dropdownParent: $('#mainModal')
});
});
});
</script>
JS;
?>
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=supply_contracts/handle_edit_ajax" class="ajax-form">
<input type="hidden" name="id" value="<?php echo $contract['id']; ?>">
<div class="row g-3">
<div class="col-sm-6">
<label for="supplier_id" class="form-label">اختر المورد</label>
<select class="form-select select2-init" id="supplier_id" name="supplier_id" required data-placeholder="ابحث عن مورد..."><option></option>
<?php foreach ($suppliers_list as $supplier): ?>
<option value="<?php echo $supplier['id']; ?>" <?php echo ($contract['supplier_id'] == $supplier['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($supplier['supplier_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-sm-6">
<label for="property_id" class="form-label">اختر العقار</label>
<select class="form-select select2-init" id="property_id" name="property_id" required data-placeholder="ابحث عن عقار..."><option></option>
<?php foreach ($properties_list as $property): ?>
<option value="<?php echo $property['id']; ?>" <?php echo ($contract['property_id'] == $property['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($property['property_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-sm-6"><label for="contract_number" class="form-label">رقم العقد المرجعي</label><input type="text" class="form-control" id="contract_number" name="contract_number" value="<?php echo htmlspecialchars($contract['contract_number']); ?>"></div>
<div class="col-sm-6"><label for="service_description" class="form-label">وصف الخدمة</label><input type="text" class="form-control" id="service_description" name="service_description" required value="<?php echo htmlspecialchars($contract['service_description']); ?>"></div>
<div class="col-sm-6"><label for="start_date" class="form-label">تاريخ البدء</label><input type="date" class="form-control" id="start_date" name="start_date" required value="<?php echo $contract['start_date']; ?>"></div>
<div class="col-sm-6"><label for="end_date" class="form-label">تاريخ الانتهاء</label><input type="date" class="form-control" id="end_date" name="end_date" required value="<?php echo $contract['end_date']; ?>"></div>
<div class="col-sm-6"><label for="total_amount" class="form-label">المبلغ الإجمالي</label><input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" required value="<?php echo htmlspecialchars($contract['total_amount']); ?>"></div>
<div class="col-sm-6"><label for="payment_cycle" class="form-label">دورة السداد</label><select class="form-select" id="payment_cycle" name="payment_cycle"><option value="دفعة واحدة" <?php echo ($contract['payment_cycle'] == 'دفعة واحدة') ? 'selected' : ''; ?>>دفعة واحدة</option><option value="شهري" <?php echo ($contract['payment_cycle'] == 'شهري') ? 'selected' : ''; ?>>شهري</option><option value="ربع سنوي" <?php echo ($contract['payment_cycle'] == 'ربع سنوي') ? 'selected' : ''; ?>>ربع سنوي</option><option value="نصف سنوي" <?php echo ($contract['payment_cycle'] == 'نصف سنوي') ? 'selected' : ''; ?>>نصف سنوي</option><option value="سنوي" <?php echo ($contract['payment_cycle'] == 'سنوي') ? 'selected' : ''; ?>>سنوي</option></select></div>
<div class="col-sm-6"><label for="status" class="form-label">الحالة</label><select class="form-select" id="status" name="status"><option value="نشط" <?php echo ($contract['status'] == 'نشط') ? 'selected' : ''; ?>>نشط</option><option value="منتهي" <?php echo ($contract['status'] == 'منتهي') ? 'selected' : ''; ?>>منتهي</option><option value="ملغي" <?php echo ($contract['status'] == 'ملغي') ? 'selected' : ''; ?>>ملغي</option></select></div>
<div class="col-12"><label for="notes" class="form-label">ملاحظات</label><textarea class="form-control" id="notes" name="notes" rows="2"><?php echo htmlspecialchars($contract['notes']); ?></textarea></div>
</div>
<hr class="my-4">
<div class="d-flex justify-content-end">
<button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
<button type="submit" class="btn btn-primary">حفظ التعديلات</button>
</div>
</form>