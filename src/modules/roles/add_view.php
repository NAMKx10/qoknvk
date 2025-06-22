<div class="modal-header">
    <h5 class="modal-title">إضافة دور جديد</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form method="POST" action="index.php?page=roles/handle_add" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="mb-3"><label class="form-label required">اسم الدور</label><input type="text" class="form-control" name="role_name" required></div>
        <div class="mb-3"><label class="form-label">الوصف</label><textarea class="form-control" name="description" rows="3"></textarea></div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ الدور</button>
    </div>
</form>