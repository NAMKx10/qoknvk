<?php
// src/modules/properties/edit_view.php (النسخة الجديدة المبسطة للإصدار 3.0)

// 1. جلب بيانات العقار المحدد
if (!isset($_GET['id'])) { die("Property ID is required."); }
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$_GET['id']]);
$property = $stmt->fetch();
if (!$property) { die("Property not found."); }

// 2. جلب الخيارات للقوائم المنسدلة
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'Active' ORDER BY branch_name ASC")->fetchAll();
$property_types = get_lookup_options($pdo, 'property_type');
$ownership_types = get_lookup_options($pdo, 'ownership_type');
$statuses = get_lookup_options($pdo, 'status', true);
?>

<div class="modal-header">
    <h5 class="modal-title">تعديل العقار: <?= htmlspecialchars($property['property_name']) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=properties/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $property['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم العقار</label><input type="text" class="form-control" name="property_name" value="<?= htmlspecialchars($property['property_name']) ?>" required></div>
            <div class="col-md-6"><label class="form-label">الفرع</label><select class="form-select select2-init" name="branch_id"><option value="">اختر...</option><?php foreach($branches_list as $b):?><option value="<?=$b['id']?>" <?= ($property['branch_id'] == $b['id'])?'selected':'' ?>><?=htmlspecialchars($b['branch_name'])?></option><?php endforeach;?></select></div>
            <div class="col-md-6"><label class="form-label">كود العقار</label><input type="text" class="form-control" name="property_code" value="<?= htmlspecialchars($property['property_code']) ?>"></div>
            
            <div class="col-md-6">
                <label class="form-label">نوع العقار</label>
                <select class="form-select select2-init" name="property_type"><option value="">اختر...</option><?php foreach($property_types as $pt):?><option value="<?=htmlspecialchars($pt)?>" <?= ($property['property_type'] == $pt)?'selected':'' ?>><?=htmlspecialchars($pt)?></option><?php endforeach;?></select>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">نوع التملك</label>
                <select class="form-select" name="ownership_type"><option value="">اختر...</option><?php foreach($ownership_types as $ot):?><option value="<?=htmlspecialchars($ot)?>" <?= ($property['ownership_type'] == $ot)?'selected':'' ?>><?=htmlspecialchars($ot)?></option><?php endforeach;?></select>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">الحالة</label>
                <select class="form-select" name="status">
                    <?php foreach($statuses as $key => $value): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= ($property['status'] === $key) ? 'selected' : '' ?>><?= htmlspecialchars($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
             <!-- ✨ تم حذف حقلي اسم المالك ورقم الصك من هنا ✨ -->

            <div class="col-md-6"><label class="form-label">قيمة العقار</label><input type="number" step="0.01" class="form-control" name="property_value" value="<?= htmlspecialchars($property['property_value']) ?>"></div>
            <div class="col-md-6"><label class="form-label">الحي</label><input type="text" class="form-control" name="district" value="<?= htmlspecialchars($property['district']) ?>"></div>
            <div class="col-md-6"><label class="form-label">المدينة</label><input type="text" class="form-control" name="city" value="<?= htmlspecialchars($property['city']) ?>"></div>
            <div class="col-md-6"><label class="form-label">المساحة (م²)</label><input type="number" step="0.01" class="form-control" name="area" value="<?= htmlspecialchars($property['area']) ?>"></div>
            <div class="col-lg-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($property['notes']) ?></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary ms-auto">حفظ التعديلات</button>
    </div>
</form>