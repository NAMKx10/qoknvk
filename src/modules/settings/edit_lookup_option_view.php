<?php
if (!isset($_GET['id'])) { die("ID is required."); }
$stmt = $pdo->prepare("SELECT * FROM lookup_options WHERE id = ?");
$stmt->execute([$_GET['id']]);
$option = $stmt->fetch();
if (!$option) { die("Option not found."); }
?>
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=settings/handle_edit_lookup_option_ajax" class="ajax-form">
<input type="hidden" name="id" value="<?php echo $option['id']; ?>">
<div class="row g-3">
<div class="col-md-6">
<label for="option_value" class="form-label">القيمة المعروضة</label>
<input type="text" class="form-control" id="option_value" name="option_value" value="<?php echo htmlspecialchars($option['option_value']); ?>" required>
</div>
<div class="col-md-6">
<label for="option_key" class="form-label">المفتاح (انجليزي)</label>
<input type="text" class="form-control" id="option_key" name="option_key" value="<?php echo htmlspecialchars($option['option_key']); ?>" required>
</div>
<div class="col-md-6">
<label for="bg_color" class="form-label">لون الخلفية</label>
<input type="color" class="form-control form-control-color" id="bg_color" name="bg_color" value="<?php echo htmlspecialchars($option['bg_color'] ?? '#6c757d'); ?>" title="اختر لون الخلفية">
</div>
<div class="col-md-6">
<label for="color" class="form-label">لون النص</label>
<input type="color" class="form-control form-control-color" id="color" name="color" value="<?php echo htmlspecialchars($option['color'] ?? '#ffffff'); ?>" title="اختر لون النص">
</div>
</div>
<hr class="my-4">
<div class="d-flex justify-content-end">
<button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
<button type="submit" class="btn btn-primary">حفظ التعديلات</button>
</div>
</form>