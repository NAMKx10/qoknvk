<div class="modal-header">
    <h5 class="modal-title">إضافة مجموعة جديدة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=settings/handle_add_lookup_group_ajax" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="mb-3">
            <label class="form-label">اسم المجموعة (للعرض)</label>
            <input type="text" class="form-control" name="option_value" placeholder="مثال: أنواع العقارات" required>
        </div>
        <div class="mb-3">
            <label class="form-label">مفتاح المجموعة (انجليزي، بدون مسافات)</label>
            <input type="text" class="form-control" name="group_key" placeholder="مثال: property_types" required>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary">حفظ المجموعة</button>
    </div>
</form>