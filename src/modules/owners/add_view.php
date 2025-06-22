<?php
// src/modules/owners/add_view.php
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'نشط' AND deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();
?>
<div class="modal-header">
    <h5 class="modal-title">إضافة مالك جديد</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=owners/handle_add" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم المالك</label><input type="text" class="form-control" name="owner_name" required></div>
            <div class="col-md-6"><label class="form-label required">نوع المالك</label><select class="form-select" name="owner_type"><option value="فرد">فرد</option><option value="منشأة">منشأة</option></select></div>
            <div class="col-md-6"><label class="form-label">كود المالك</label><input type="text" class="form-control" name="owner_code"></div>
            <div class="col-md-6"><label class="form-label">رقم الهوية/السجل</label><input type="text" class="form-control" name="id_number"></div>
            <div class="col-md-6"><label class="form-label">الجوال</label><input type="text" class="form-control" name="mobile"></div>
            <div class="col-md-6"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email"></div>
            <div class="col-12"><label class="form-label">الفروع المرتبطة (مطلوب)</label><select class="form-select select2-init" name="branches[]" multiple required><?php foreach($branches_list as $b):?><option value="<?=$b['id']?>"><?=htmlspecialchars($b['branch_name'])?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary">حفظ المالك</button>
    </div>
</form>