<?php
// src/modules/clients/add_view.php (النسخة المحدثة مع كود العميل)
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'Active' AND deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();
$client_types = get_lookup_options($pdo, 'entity_type');
?>
<div class="modal-header">
    <h5 class="modal-title">إضافة عميل جديد</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=clients/handle_add" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم العميل</label><input type="text" class="form-control" name="client_name" required></div>
            <div class="col-md-6"><label class="form-label">كود العميل</label><input type="text" class="form-control" name="client_code"></div>
            <div class="col-md-6"><label class="form-label required">نوع العميل</label><select class="form-select" name="client_type" required><?php foreach($client_types as $type): ?><option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">رقم الهوية/السجل</label><input type="text" class="form-control" name="id_number"></div>
            <div class="col-md-6"><label class="form-label">الرقم الضريبي</label><input type="text" class="form-control" name="tax_number"></div>
            <div class="col-md-6"><label class="form-label">الجوال</label><input type="text" class="form-control" name="mobile"></div>
            <div class="col-md-6"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email"></div>
            <div class="col-md-6"><label class="form-label">اسم الممثل</label><input type="text" class="form-control" name="representative_name"></div>
            <div class="col-12"><label class="form-label">الفروع المرتبطة</label><select class="form-select select2-init" name="branches[]" multiple data-placeholder="اختر فرعًا أو أكثر..."><?php foreach ($branches_list as $branch): ?><option value="<?= $branch['id']; ?>"><?= htmlspecialchars($branch['branch_name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">العنوان</label><textarea class="form-control" name="address" rows="2"></textarea></div>
            <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ العميل</button></div>
</form>