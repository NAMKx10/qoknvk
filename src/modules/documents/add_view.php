<?php
// src/modules/documents/add_view.php (الإصدار المصحح والنهائي جدًا)

// --- (مُصحَّح) الاستعلام يستخدم الآن 'documents_type' الصحيح ---
$stmt = $pdo->prepare("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'documents_type' AND deleted_at IS NULL");
$stmt->execute();
$all_options = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- (مُحسَّن) الفلترة الآن تتم بشكل صحيح ومضمون ---
$document_types = array_filter($all_options, function($option) {
    // استبعاد السجل الذي يمثل اسم المجموعة نفسها
    return $option['option_key'] !== 'documents_type'; 
});
?>

<div class="modal-header">
    <h5 class="modal-title">إضافة وثيقة جديدة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<!-- بقية الملف (النموذج والجافاسكريبت) يبقى كما هو تمامًا -->

<form method="POST" action="index.php?page=documents/handle_add" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label required">نوع الوثيقة</label>
                <select class="form-select" name="document_type" id="document-type-select" required>
                    <option value="" disabled selected>-- اختر النوع --</option>
                    <?php foreach($document_types as $type): ?>
                        <option value="<?= htmlspecialchars($type['option_key']) ?>"><?= htmlspecialchars($type['option_value']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">رقم الوثيقة</label>
                <input type="text" class="form-control" name="document_number">
            </div>
             <div class="col-md-6 mb-3">
                <label class="form-label">تاريخ الإصدار</label>
                <input type="date" class="form-control" name="issue_date">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">تاريخ الانتهاء</label>
                <input type="date" class="form-control" name="expiry_date">
            </div>
        </div>
        
        <hr class="my-3">

        <div id="custom-fields-wrapper" style="display:none;">
            <h4 class="mb-3">تفاصيل إضافية</h4>
            <div id="custom-fields-container" class="row g-3"></div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ الوثيقة</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#document-type-select').on('change', function() {
        const typeKey = $(this).val();
        const container = $('#custom-fields-container');
        const wrapper = $('#custom-fields-wrapper');

        if (!typeKey) {
            wrapper.slideUp();
            container.empty();
            return;
        }
        
        $.ajax({
            url: 'index.php?page=documents/get_custom_fields_schema_ajax',
            type: 'GET',
            data: { document_type: typeKey },
            dataType: 'json',
            success: function(fields) {
                container.empty();
                if (fields && fields.length > 0) {
                    fields.forEach(function(field) {
                        let required_html = field.required ? 'required' : '';
                        let required_star = field.required ? ' <span class="text-danger">*</span>' : '';
                        let field_html = `
                            <div class="col-md-6">
                                <label class="form-label">${field.label}${required_star}</label>
                                <input type="${field.type}" class="form-control" name="details[${field.name}]" ${required_html}>
                            </div>
                        `;
                        container.append(field_html);
                    });
                    wrapper.slideDown();
                } else {
                    wrapper.slideUp();
                }
            }
        });
    });
});
</script>