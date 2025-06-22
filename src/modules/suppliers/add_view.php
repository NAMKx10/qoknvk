<?php
// جلب قائمة الفروع النشطة للاختيار
$branches_stmt = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'نشط' ORDER BY branch_name ASC");
$branches_list = $branches_stmt->fetchAll();
?>

<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>

<form method="POST" action="index.php?page=suppliers/handle_add_ajax" class="ajax-form">
    <div class="row g-3">
        <div class="col-sm-6">
            <label for="supplier_name" class="form-label">اسم المورد</label>
            <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
        </div>
        <div class="col-sm-6">
            <label for="supplier_type" class="form-label">نوع المورد</label>
            <select class="form-select" id="supplier_type" name="supplier_type">
            <option value="منشأة" selected>منشأة</option>
            <option value="فرد">فرد</option>
            </select>
        </div>
        <div class="col-12">
            <label for="branches" class="form-label">الفروع المرتبطة (اختياري)</label>
            <select class="form-select select2-init" id="branches" name="branches[]" multiple data-placeholder="اختر فرعًا أو أكثر...">
                <?php foreach ($branches_list as $branch): ?>
                    <option value="<?php echo $branch['id']; ?>"><?php echo htmlspecialchars($branch['branch_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6">
            <label for="service_type" class="form-label">الخدمة المقدمة</label>
            <input type="text" class="form-control" id="service_type" name="service_type" placeholder="مثال: صيانة مصاعد، نظافة...">
        </div>
        <div class="col-sm-6">
            <label for="registration_number" class="form-label">رقم السجل التجاري</label>
            <input type="text" class="form-control" id="registration_number" name="registration_number">
        </div>
        <div class="col-sm-6">
            <label for="tax_number" class="form-label">الرقم الضريبي</label>
            <input type="text" class="form-control" id="tax_number" name="tax_number">
        </div>
        <div class="col-sm-6">
            <label for="contact_person" class="form-label">مسؤول التواصل</label>
            <input type="text" class="form-control" id="contact_person" name="contact_person">
        </div>
        <div class="col-sm-6">
            <label for="mobile" class="form-label">الجوال</label>
            <input type="text" class="form-control" id="mobile" name="mobile">
        </div>
        <div class="col-sm-6">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input type="email" class="form-control" id="email" name="email">
        </div>
        <div class="col-12">
            <label for="address" class="form-label">العنوان</label>
            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
        </div>
    </div>
        <div class="col-12">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
         </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ المورد</button>
    </div>
</form>