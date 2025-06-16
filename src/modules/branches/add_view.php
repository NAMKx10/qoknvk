<div class="modal-header">
  <h5 class="modal-title">إضافة فرع جديد</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=branches/handle_add">
    <div class="modal-body">
        <div class="row">
            <div class="col-lg-6 mb-3"><label class="form-label required">اسم الفرع/الشركة</label><input type="text" class="form-control" name="branch_name" required></div>
            <div class="col-lg-6 mb-3"><label class="form-label">كود الفرع (فريد)</label><input type="text" class="form-control" name="branch_code"></div>
            <div class="col-lg-6 mb-3"><label class="form-label">نوع الكيان</label><select class="form-select" name="branch_type"><option value="منشأة" selected>منشأة</option><option value="فرد">فرد</option></select></div>
            <div class="col-lg-6 mb-3"><label class="form-label">رقم السجل</label><input type="text" class="form-control" name="registration_number"></div>
            <div class="col-lg-6 mb-3"><label class="form-label">الرقم الضريبي</label><input type="text" class="form-control" name="tax_number"></div>
            <div class="col-lg-6 mb-3"><label class="form-label">الجوال/الهاتف</label><input type="text" class="form-control" name="phone"></div>
            <div class="col-lg-12 mb-3"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email"></div>
            <div class="col-lg-12 mb-3"><label class="form-label">العنوان</label><textarea class="form-control" name="address" rows="2"></textarea></div>
            <div class="col-lg-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary ms-auto">حفظ الفرع</button>
    </div>
</form>
