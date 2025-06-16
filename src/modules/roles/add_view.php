<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=roles/handle_add_ajax" class="ajax-form">
    <div class="mb-3"><label for="role_name" class="form-label">اسم الدور</label><input type="text" class="form-control" id="role_name" name="role_name" required></div>
    <div class="mb-3"><label for="description" class="form-label">الوصف</label><textarea class="form-control" id="description" name="description" rows="3"></textarea></div>
    <hr><div class="d-flex justify-content-end"><button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ الدور</button></div>
</form>