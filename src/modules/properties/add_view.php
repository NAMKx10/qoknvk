<?php
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'نشط'")->fetchAll();
$property_types = $pdo->query("SELECT option_value FROM lookup_options WHERE group_key = 'property_type'")->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="modal-header">
    <h5 class="modal-title">إضافة عقار جديد</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form method="POST" action="index.php?page=properties/handle_add">
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم العقار</label><input type="text" class="form-control" name="property_name" required></div>
            <div class="col-md-6"><label class="form-label">الفرع التابع له</label><select class="form-select" name="branch_id"><option value="">اختر الفرع...</option><?php foreach($branches_list as $b):?><option value="<?=$b['id']?>"><?=$b['branch_name']?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">كود العقار</label><input type="text" class="form-control" name="property_code"></div>
            <div class="col-md-6"><label class="form-label">نوع العقار</label><select class="form-select" name="property_type"><option value="">اختر النوع...</option><?php foreach($property_types as $pt):?><option value="<?=$pt?>"><?=$pt?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">نوع التملك</label><select class="form-select" name="ownership_type"><option value="ملك">ملك</option><option value="استثمار">استثمار</option></select></div>
            <div class="col-md-6"><label class="form-label">الحالة</label><select class="form-select" name="status"><option value="نشط">نشط</option><option value="ملغي">ملغي</option></select></div>
            <div class="col-md-6"><label class="form-label">اسم المالك</label><input type="text" class="form-control" name="owner_name"></div>
            <div class="col-md-6"><label class="form-label">رقم الصك</label><input type="text" class="form-control" name="deed_number"></div>
            <div class="col-md-6"><label class="form-label">قيمة العقار</label><input type="number" class="form-control" name="property_value"></div>
            <div class="col-md-6"><label class="form-label">الحي</label><input type="text" class="form-control" name="district"></div>
            <div class="col-md-6"><label class="form-label">المدينة</label><input type="text" class="form-control" name="city"></div>
            <div class="col-md-6"><label class="form-label">المساحة (م²)</label><input type="number" class="form-control" name="area"></div>
            <div class="col-lg-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="3"></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary ms-auto">حفظ العقار</button>
    </div>
</form>