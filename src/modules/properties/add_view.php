<?php
// src/modules/properties/add_view.php (النسخة الجديدة المبسطة للإصدار 3.0)

// 1. جلب الخيارات اللازمة للقوائم المنسدلة باستخدام الدوال المركزية
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'Active' ORDER BY branch_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$property_types = get_lookup_options($pdo, 'property_type');
$ownership_types = get_lookup_options($pdo, 'ownership_type');
$statuses = get_lookup_options($pdo, 'status', true);
?>

<div class="modal-header">
    <h5 class="modal-title">إضافة عقار جديد</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=properties/handle_add" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم العقار</label><input type="text" class="form-control" name="property_name" required></div>
            <div class="col-md-6"><label class="form-label required">الفرع التابع له</label><select class="form-select select2-init" name="branch_id" required><option value="">اختر الفرع...</option><?php foreach($branches_list as $b):?><option value="<?=$b['id']?>"><?=htmlspecialchars($b['branch_name'])?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">كود العقار</label><input type="text" class="form-control" name="property_code"></div>
            
            <div class="col-md-6">
                <label class="form-label">نوع العقار</label>
                <select class="form-select select2-init" name="property_type">
                    <option value="">اختر النوع...</option>
                    <?php foreach($property_types as $pt):?>
                        <option value="<?=htmlspecialchars($pt)?>"><?=htmlspecialchars($pt)?></option>
                    <?php endforeach;?>
                </select>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">نوع التملك</label>
                <select class="form-select" name="ownership_type">
                    <option value="">اختر...</option>
                     <!-- ✨ هنا الربط بتهيئة المدخلات ✨ -->
                    <?php foreach($ownership_types as $ot):?>
                        <option value="<?=htmlspecialchars($ot)?>"><?=htmlspecialchars($ot)?></option>
                    <?php endforeach;?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">الحالة</label>
                <select class="form-select" name="status">
                    <!-- ✨ هنا الربط بتهيئة المدخلات ✨ -->
                    <?php foreach($statuses as $key => $value): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= ($key === 'Active') ? 'selected' : '' ?>>
                            <?= htmlspecialchars($value) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- ✨ تم حذف حقلي اسم المالك ورقم الصك من هنا ✨ -->
            
            <div class="col-md-6"><label class="form-label">قيمة العقار</label><input type="number" step="0.01" class="form-control" name="property_value"></div>
            <div class="col-md-6"><label class="form-label">الحي</label><input type="text" class="form-control" name="district"></div>
            <div class="col-md-6"><label class="form-label">المدينة</label><input type="text" class="form-control" name="city"></div>
            <div class="col-md-6"><label class="form-label">المساحة (م²)</label><input type="number" step="0.01" class="form-control" name="area"></div>
            <div class="col-lg-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="3"></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary ms-auto"><i class="ti ti-plus me-2"></i>حفظ العقار</button>
    </div>
</form>