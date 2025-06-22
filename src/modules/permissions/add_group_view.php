<div class="modal-header">
    <h5 class="modal-title">إضافة مجموعة صلاحيات جديدة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form method="POST" action="index.php?page=permissions/handle_add_group" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="mb-3">
            <label class="form-label required">اسم المجموعة (للعرض)</label>
            <input type="text" class="form-control" name="group_name" placeholder="مثال: إدارة العقود" required>
        </div>
        <!-- (جديد ومُصحَّح) إضافة حقل المفتاح البرمجي -->
        <div class="mb-3">
            <label class="form-label required">المفتاح البرمجي (انجليزي، فريد)</label>
            <input type="text" class="form-control" name="group_key" placeholder="مثال: contracts_management" required>
        </div>
        <div class="mb-3">
            <label class="form-label">ترتيب العرض</label>
            <input type="number" class="form-control" name="display_order" value="0">
        </div>
        <div class="mb-3">
            <label class="form-label">الوصف (اختياري)</label>
            <textarea class="form-control" name="description" rows="2"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ</button>
    </div>
</form>