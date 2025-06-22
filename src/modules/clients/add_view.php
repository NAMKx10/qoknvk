<?php
// جلب قائمة الفروع النشطة للاختيار
$branches_stmt = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'نشط' ORDER BY branch_name ASC");
$branches_list = $branches_stmt->fetchAll();

// هذا الكود لتفعيل Select2 بشكل صحيح داخل النافذة المنبثقة
$page_scripts = <<<JS
<script>
$(document).ready(function() {
    // الكود المركزي في footer.php سيتولى التفعيل
    // لكن نحتاج للتأكد من أن السياق صحيح
    if ($('#mainModal').data('bs.modal')?.isShown) {
         $('#mainModal .select2-init').select2({
            theme: 'bootstrap-5',
            dir: "rtl",
            placeholder: $(this).data('placeholder'),
            dropdownParent: $('#mainModal')
        });
    }
});
</script>
JS;
?>

<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>

<form method="POST" action="index.php?page=clients/handle_add_ajax" class="ajax-form">
    <div class="row g-3">
        <div class="col-sm-6">
            <label for="client_name" class="form-label">اسم العميل/المنشأة</label>
            <input type="text" class="form-control" id="client_name" name="client_name" required>
        </div>
        <div class="col-sm-6">
            <label for="client_type" class="form-label">نوع العميل</label>
            <select class="form-select" id="client_type" name="client_type">
                <option value="فرد" selected>فرد</option>
                <option value="منشأة">منشأة</option>
            </select>
        </div>
        <div class="col-sm-6">
            <label for="id_number" class="form-label">رقم الهوية/السجل</label>
            <input type="text" class="form-control" id="id_number" name="id_number">
        </div>
        <div class="col-sm-6">
            <label for="tax_number" class="form-label">الرقم الضريبي</label>
            <input type="text" class="form-control" id="tax_number" name="tax_number">
        </div>
        <div class="col-sm-6">
            <label for="mobile" class="form-label">الجوال</label>
            <input type="text" class="form-control" id="mobile" name="mobile">
        </div>
        <div class="col-sm-6">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input type="email" class="form-control" id="email" name="email">
        </div>
        <div class="col-sm-6">
            <label for="representative_name" class="form-label">اسم الممثل (للمنشآت)</label>
            <input type="text" class="form-control" id="representative_name" name="representative_name">
        </div>
        <div class="col-sm-6">
    <!-- اتركه فارغًا للحفاظ على التنسيق -->
</div>
        <div class="col-12">
            <label for="branches" class="form-label">الفروع المرتبطة (اختياري)</label>
            <select class="form-select select2-init" id="branches" name="branches[]" multiple data-placeholder="اختر فرعًا أو أكثر...">
                <?php foreach ($branches_list as $branch): ?>
                    <option value="<?php echo $branch['id']; ?>"><?php echo htmlspecialchars($branch['branch_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label for="address" class="form-label">العنوان الوطني</label>
            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
        </div>
        <div class="col-12">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
        </div>
    </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ العميل</button>
    </div>
</form>