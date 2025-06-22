<?php $group_id = $_GET['group_id'] ?? 0; ?>
<div class="modal-header"><h5 class="modal-title">إضافة صلاحية جديدة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="index.php?page=permissions/handle_add" class="ajax-form">
    <input type="hidden" name="group_id" value="<?= $group_id ?>">
    <div class="modal-body">
         <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
         <div class="mb-3"><label class="form-label required">الوصف</label><input type="text" class="form-control" name="description" placeholder="مثال: إضافة عقار جديد" required></div>
         <div class="mb-3"><label class="form-label required">المفتاح (انجليزي)</label><input type="text" class="form-control" name="permission_key" placeholder="مثال: add_property" required></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ</button></div>
</form>