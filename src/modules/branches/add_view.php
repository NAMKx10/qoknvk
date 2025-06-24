<?php
// --- جلب البيانات من الإعدادات ---
$branch_types_options = $pdo->query("SELECT option_value FROM lookup_options WHERE group_key = 'entity_type' AND option_key != 'entity_type' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="modal-header">
    <h5 class="modal-title">إضافة فرع جديد</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=branches/handle_add" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم الفرع/الشركة</label><input type="text" class="form-control" name="branch_name" required></div>
            <div class="col-md-6"><label class="form-label">كود الفرع</label><input type="text" class="form-control" name="branch_code"></div>
            <div class="col-md-6">
                <label class="form-label">نوع الكيان</label>
                <select class="form-select select2-init" name="branch_type">
                    <?php foreach($branch_types_options as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">رقم السجل التجاري</label><input type="text" class="form-control" name="registration_number"></div>
            <div class="col-md-6"><label class="form-label">الرقم الضريبي</label><input type="text" class="form-control" name="tax_number"></div>
            <div class="col-md-6"><label class="form-label">الجوال/الهاتف</label><input type="text" class="form-control" name="phone"></div>
            <div class="col-12"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email"></div>
            <div class="col-12"><label class="form-label">العنوان</label><textarea class="form-control" name="address" rows="2"></textarea></div>
            <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <!-- تم توحيد الأزرار مؤقتًا، سيتم تفعيل منطق المسودة لاحقًا -->
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary ms-auto"><i class="ti ti-plus me-2"></i>حفظ الفرع</button>
    </div>
</form>