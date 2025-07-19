<?php
// src/modules/units/add_view.php (النسخة المصححة)
global $pdo;
$properties_list = $pdo->query("SELECT id, property_name FROM properties WHERE status = 'Active' AND deleted_at IS NULL ORDER BY property_name ASC")->fetchAll();
$unit_types = get_lookup_options($pdo, 'unit_type');
$statuses = get_lookup_options($pdo, 'status', true);
?>
<form method="POST" action="index.php?page=units/handle_add" class="ajax-form">
    <div class="modal-header"><h5 class="modal-title">إضافة وحدة جديدة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-sm-6"><label class="form-label required">العقار</label><select class="form-select select2-init" name="property_id" required data-placeholder="اختر..."><option></option><?php foreach ($properties_list as $p):?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['property_name'])?></option><?php endforeach;?></select></div>
            <div class="col-sm-6"><label class="form-label required">اسم الوحدة</label><input type="text" class="form-control" name="unit_name" required></div>
            <div class="col-sm-6"><label class="form-label">رقم الوحدة</label><input type="text" class="form-control" name="unit_number"></div>
            <div class="col-sm-6"><label class="form-label">كود الوحدة</label><input type="text" class="form-control" name="unit_code"></div>
            <div class="col-sm-6"><label class="form-label">نوع الوحدة</label><select class="form-select select2-init" name="unit_type"><option value="">اختر...</option><?php foreach($unit_types as $type):?><option value="<?=htmlspecialchars($type)?>"><?=htmlspecialchars($type)?></option><?php endforeach;?></select></div>
            <div class="col-sm-6"><label class="form-label">المساحة (م²)</label><input type="number" step="0.01" class="form-control" name="area"></div>
            <div class="col-sm-6"><label class="form-label">الدور</label><input type="number" class="form-control" name="floor"></div>
            <div class="col-sm-6">
                <label class="form-label">الحالة</label>
                <select class="form-select" name="status">
                    <?php foreach ($statuses as $key => $value): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= ($key === 'Available') ? 'selected' : '' ?>><?= htmlspecialchars($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ</button></div>
</form>