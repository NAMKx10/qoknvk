<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=settings/handle_add_lookup_group_ajax" class="ajax-form">
    <div class="mb-3">
        <label for="option_value" class="form-label">اسم المجموعة (للعرض)</label>
        <input type="text" class="form-control" id="option_value" name="option_value" placeholder="مثال: أنواع العقارات" required>
    </div>
    <div class="mb-3">
        <label for="group_key" class="form-label">مفتاح المجموعة (انجليزي، بدون مسافات)</label>
        <input type="text" class="form-control" id="group_key" name="group_key" placeholder="مثال: property_types" required>
    </div>
    <hr>
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ المجموعة</button>
    </div>
</form>