<?php
// src/modules/settings/add_lookup_option_view.php (الإصدار المطور)
$group_key = $_GET['group'] ?? '';
?>
<div class="modal-header">
    <h5 class="modal-title">إضافة خيار جديد إلى "<?= htmlspecialchars($group_key) ?>"</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form method="POST" action="index.php?page=settings/handle_add_lookup_option_ajax" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <input type="hidden" name="group_key" value="<?= htmlspecialchars($group_key) ?>">
        
        <div class="mb-3">
            <label class="form-label required">القيمة المعروضة</label>
            <input type="text" class="form-control" name="option_value" placeholder="مثال: شقة، مكتب، صك ملكية" required>
        </div>
        <div class="mb-3">
            <label class="form-label">المفتاح البرمجي (انجليزي، بدون مسافات)</label>
            <input type="text" class="form-control" name="option_key" placeholder="مثال: apartment, deed">
            <div class="form-text">اختياري. إذا ترك فارغاً سيتم إنشاؤه تلقائياً.</div>
        </div>
    </div>

    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary">حفظ الخيار</button>
    </div>
</form>