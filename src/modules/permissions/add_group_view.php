<?php
// src/modules/permissions/add_group_view.php
?>
<div class="modal-header">
    <h5 class="modal-title">إضافة مجموعة صلاحيات جديدة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=permissions/handle_add_group" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="mb-3">
            <label class="form-label required">اسم المجموعة</label>
            <input type="text" class="form-control" name="group_name" placeholder="مثال: إدارة العقود" required>
        </div>
        <div class="mb-3">
            <label class="form-label">ترتيب العرض</label>
            <input type="number" class="form-control" name="display_order" value="0">
            <small class="form-hint">يستخدم لترتيب المجموعات في القائمة. الأرقام الأقل تظهر أولاً.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">الوصف (اختياري)</label>
            <textarea class="form-control" name="description" rows="2" placeholder="وصف موجز لمحتوى هذه المجموعة..."></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ المجموعة</button>
    </div>
</form>