<?php
// لا نحتاج لأي كود PHP هنا لأننا لا نجلب بيانات
?>
<form method="POST" action="index.php?page=branches/handle_add_ajax" class="ajax-form">
    <!-- رسالة الخطأ -->
    <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>

    <div class="row">
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label required">اسم الفرع/الشركة</label>
                <input type="text" class="form-control" name="branch_name" required>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label">كود الفرع (فريد)</label>
                <input type="text" class="form-control" name="branch_code">
            </div>
        </div>
        <div class="col-lg-6">
             <div class="mb-3">
                <label class="form-label">نوع الكيان</label>
                <select class="form-select" name="branch_type">
                    <option value="منشأة" selected>منشأة</option>
                    <option value="فرد">فرد</option>
                </select>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label">رقم السجل</label>
                <input type="text" class="form-control" name="registration_number">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label">الرقم الضريبي</label>
                <input type="text" class="form-control" name="tax_number">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label">الجوال/الهاتف</label>
                <input type="text" class="form-control" name="phone">
            </div>
        </div>
        <div class="col-lg-12">
            <div class="mb-3">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" class="form-control" name="email">
            </div>
        </div>
        <div class="col-lg-12">
            <div>
                <label class="form-label">ملاحظات</label>
                <textarea class="form-control" name="notes" rows="3"></textarea>
            </div>
        </div>
    </div>

    <!-- أزرار الإجراءات في الأسفل -->
    <div class="modal-footer mt-4">
        <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">
            إلغاء
        </a>
        <button type="submit" class="btn btn-primary ms-auto">
            <i class="ti ti-plus me-2"></i>
            حفظ وإنشاء الفرع
        </button>
    </div>
</form>