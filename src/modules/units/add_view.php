<?php
// جلب قائمة العقارات النشطة
$properties_stmt = $pdo->query("SELECT id, property_name FROM properties WHERE status = 'نشط' ORDER BY property_name ASC");
$properties_list = $properties_stmt->fetchAll();

// جلب أنواع الوحدات ديناميكياً من جدول الإعدادات
$unit_types_stmt = $pdo->prepare("SELECT option_value FROM lookup_options WHERE group_key = ? AND group_key != option_key AND deleted_at IS NULL ORDER BY display_order, option_value ASC");
$unit_types_stmt->execute(['unit_type']);
$unit_types = $unit_types_stmt->fetchAll(PDO::FETCH_COLUMN);
// ----------------------

// تفعيل Select2 لهذه الصفحة
$page_scripts = <<<JS
<script>
$(document).ready(function() {
    // Note: The initialization logic is now in footer.php
    // We just need to add the class 'select2-init' to the select element.
});
</script>
JS;
?>

<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>

<form method="POST" action="index.php?page=units/handle_add_ajax" class="ajax-form">
    <div class="row g-3">
        <div class="col-sm-6">
            <label for="property_id" class="form-label">اختر العقار</label>
            <select class="form-select select2-init" id="property_id" name="property_id" required data-placeholder="ابحث عن عقار...">
                <option></option>
                <?php foreach ($properties_list as $property): ?>
                    <option value="<?php echo $property['id']; ?>"><?php echo htmlspecialchars($property['property_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6">
            <label for="unit_name" class="form-label">اسم/رقم الوحدة</label>
            <input type="text" class="form-control" id="unit_name" name="unit_name" placeholder="مثال: مكتب 101" required>
        </div>
        <div class="col-sm-6"><label for="unit_code" class="form-label">كود الوحدة</label><input type="text" class="form-control" id="unit_code" name="unit_code"></div>
        <div class="col-sm-6">
    <label for="unit_type" class="form-label">نوع الوحدة</label>
    <select class="form-select select2-init" id="unit_type" name="unit_type" required data-placeholder="اختر نوع الوحدة...">
        <option></option>
        <?php foreach ($unit_types as $type): ?>
            <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
        <?php endforeach; ?>
    </select>
</div>
        <div class="col-sm-6"><label for="area" class="form-label">المساحة (م²)</label><input type="number" step="0.01" class="form-control" id="area" name="area"></div>
        <div class="col-sm-6"><label for="floor" class="form-label">الدور</label><input type="number" class="form-control" id="floor" name="floor"></div>
        <div class="col-sm-6"><label for="status" class="form-label">الحالة</label><select class="form-select" id="status" name="status"><option value="متاحة" selected>متاحة</option><option value="مؤجرة">مؤجرة</option><option value="ملغاة">ملغاة</option></select></div>
        <div class="col-12"><label for="notes" class="form-label">ملاحظات</label><textarea class="form-control" id="notes" name="notes" rows="3"></textarea></div>
    </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ الوحدة</button>
    </div>
</form>
