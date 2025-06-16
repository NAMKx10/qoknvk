<?php
$group_key = $_GET['group'] ?? '';
?>
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=settings/handle_add_lookup_option_ajax" class="ajax-form">
    <div class="mb-3">
        <label for="group_key" class="form-label">اسم المجموعة</label>
        <input type="text" class="form-control" id="group_key" name="group_key" value="<?php echo htmlspecialchars($group_key); ?>" readonly>
    </div>
    <div class="mb-3">
        <label for="option_value" class="form-label">القيمة المعروضة (مثال: شقة، مكتب)</label>
        <input type="text" class="form-control" id="option_value" name="option_value" required>
    </div>
    <div class="mb-3">
        <label for="option_key" class="form-label">المفتاح (بالانجليزية، بدون مسافات)</label>
        <input type="text" class="form-control" id="option_key" name="option_key" placeholder="مثال: apartment, office_space">
        <div class="form-text">اختياري. إذا ترك فارغاً سيتم إنشاؤه تلقائياً.</div>
    </div>
    <hr>
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ الخيار</button>
    </div>
</form>