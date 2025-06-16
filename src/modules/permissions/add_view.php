<?php
$group_id = $_GET['group_id'] ?? 0;
?>
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=permissions/handle_add_ajax" class="ajax-form">
    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
    <div class="row g-3">
        <div class="col-md-6"><label for="description" class="form-label">الوصف</label><input type="text" class="form-control" id="description" placeholder="مثال: إضافة عقار جديد" name="description" required></div>
        <div class="col-md-6"><label for="permission_key" class="form-label">المفتاح (انجليزي)</label><input type="text" class="form-control" id="permission_key" placeholder="مثال: add_property" name="permission_key" required></div>
    </div>
    <hr class="my-4"><div class="d-flex justify-content-end"><button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ الصلاحية</button></div>
</form>