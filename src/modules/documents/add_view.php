<?php
// src/modules/documents/add_view.php (الإصدار النهائي والموحد)

// --- 1. جلب البيانات اللازمة للقوائم المنسدلة ---
$document_types = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'documents_type' AND option_key != 'documents_type' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_ASSOC);
$statuses = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'status' AND option_key != 'status' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_ASSOC);
$branches = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- =============================================== -->
<!-- HTML: نموذج الإضافة الموحد                      -->
<!-- =============================================== -->

<div class="modal-header">
    <h5 class="modal-title">إضافة وثيقة جديدة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form method="POST" action="index.php?page=documents/handle_add" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        
        <!-- البيانات الأساسية -->
        <fieldset class="form-fieldset">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label required">نوع الوثيقة</label><select class="form-select" name="document_type" id="document-type-select" required><option value="" disabled selected>-- اختر النوع --</option><?php foreach($document_types as $type): ?><option value="<?= htmlspecialchars($type['option_key']) ?>"><?= htmlspecialchars($type['option_value']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">اسم الوثيقة</label><input type="text" class="form-control" name="document_name"></div>
                <div class="col-md-6"><label class="form-label">رقم الوثيقة</label><input type="text" class="form-control" name="document_number"></div>
                <div class="col-md-6"><label class="form-label">الحالة</label><select class="form-select" name="status"><?php foreach($statuses as $status): ?><option value="<?= htmlspecialchars($status['option_key']) ?>" <?= ($status['option_key'] == 'active') ? 'selected' : '' ?>><?= htmlspecialchars($status['option_value']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">تاريخ الإصدار</label><input type="date" class="form-control" name="issue_date"></div>
                <div class="col-md-6"><label class="form-label">تاريخ الانتهاء</label><input type="date" class="form-control" name="expiry_date"></div>
                <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
            </div>
        </fieldset>
        
        <!-- الحقول المخصصة -->
        <div id="custom-fields-wrapper" style="display:none;"><hr><fieldset class="form-fieldset"><legend>تفاصيل إضافية</legend><div id="custom-fields-container" class="row g-3"></div></fieldset></div>
        
        <!-- قسم إدارة الروابط -->
        <fieldset class="form-fieldset mt-3">
            <legend>الكيانات المرتبطة (اختياري)</legend>
            
            <!-- جدول لعرض الروابط المؤقتة -->
            <div id="linked-entities-table" class="mb-3">
                <table class="table table-sm table-hover table-striped mb-0">
                    <thead><tr><th>الفرع</th><th>نوع الكيان</th><th>اسم الكيان</th><th class="w-1"></th></tr></thead>
                    <tbody id="linked-entities-tbody">
                        <tr id="no-links-row"><td colspan="4" class="text-center text-muted p-3">لم يتم ربط أي كيانات.</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- نموذج إضافة رابط جديد -->
            <div class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label">1. الفرع (اختياري)</label><select class="form-select" id="branch-select-filter"><option value="">-- كل الفروع --</option><?php foreach($branches as $branch): ?><option value="<?= $branch['id'] ?>"><?= htmlspecialchars($branch['branch_name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">2. نوع الكيان</label><select class="form-select" id="entity-type-select"><option value="">-- اختر --</option><option value="property">عقار</option><option value="owner">مالك</option><option value="client">عميل</option><option value="supplier">مورد</option><option value="branch">فرع</option></select></div>
                <div class="col-md-4"><label class="form-label">3. اختر الكيان</label><select class="form-select" id="entity-id-select" disabled></select></div>
                <div class="col-auto"><button type="button" class="btn btn-primary" id="add-link-btn" disabled>إضافة رابط</button></div>
            </div>
        </fieldset>

    </div>
    <div class="modal-footer"><button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ الوثيقة</button></div>
</form>

<!-- =============================================== -->
<!-- JavaScript: كود الإضافة الكامل                  -->
<!-- =============================================== -->
<script>
$(document).ready(function() {
    // --- منطق الحقول المخصصة ---
    $('#document-type-select').on('change', function() {
        const typeKey = $(this).val();
        const container = $('#custom-fields-container');
        const wrapper = $('#custom-fields-wrapper');
        if (!typeKey) { wrapper.slideUp(); container.empty(); return; }
        $.ajax({
            url: 'index.php?page=documents/get_custom_fields_schema_ajax', type: 'GET', data: { document_type: typeKey }, dataType: 'json',
            success: function(fields) {
                container.empty();
                if (fields && fields.length > 0) {
                    fields.forEach(function(field) {
                        let required_html = field.required ? 'required' : '';
                        let required_star = field.required ? ' <span class="text-danger">*</span>' : '';
                        let field_html = `<div class="col-md-6"><label class="form-label">${field.label}${required_star}</label><input type="${field.type}" class="form-control" name="details[${field.name}]" ${required_html}></div>`;
                        container.append(field_html);
                    });
                    wrapper.slideDown();
                } else { wrapper.slideUp(); }
            }
        });
    });

    // --- منطق ربط الكيانات ---
    const $entityTypeSelect = $('#entity-type-select');
    const $entityIdSelect = $('#entity-id-select');
    const $addLinkBtn = $('#add-link-btn');
    const $branchFilter = $('#branch-select-filter');
    const $linkedTableBody = $('#linked-entities-tbody');
    const $noLinksRow = $('#no-links-row');
    const entityTypeNames = {'property': 'عقار', 'owner': 'مالك', 'client': 'عميل', 'supplier': 'مورد', 'branch': 'فرع'};

    function initializeEntitySelect() { $entityIdSelect.select2({ theme: "bootstrap-5", dir: "rtl", placeholder: "ابحث أو اختر...", dropdownParent: $entityIdSelect.closest('.modal') }); }
    initializeEntitySelect();

    function fetchEntities() {
        const entityType = $entityTypeSelect.val();
        const branchId = $branchFilter.val();
        $entityIdSelect.empty().prop('disabled', true); $addLinkBtn.prop('disabled', true);
        if (!entityType) return;
        $.ajax({ url: 'index.php?page=documents/get_entities_for_linking_ajax', type: 'GET', data: { type: entityType, branch_id: branchId }, dataType: 'json',
            success: function(entities) {
                $entityIdSelect.append(new Option('', ''));
                entities.forEach(function(entity) { $entityIdSelect.append(new Option(entity.text, entity.id)); });
                $entityIdSelect.prop('disabled', false); $addLinkBtn.prop('disabled', false); $entityIdSelect.val(null).trigger('change');
            }
        });
    }
    
    $entityTypeSelect.on('change', fetchEntities);
    $branchFilter.on('change', fetchEntities);

    $addLinkBtn.on('click', function() {
        const entityType = $entityTypeSelect.val();
        const entityId = $entityIdSelect.val();
        const entityText = $entityIdSelect.find('option:selected').text();
        const branchText = $branchFilter.find('option:selected').text() || '<span class="text-muted">—</span>';
        
        if (!entityType || !entityId) return;

        $noLinksRow.hide();
        
        const hiddenInputs = `
            <input type="hidden" name="links[${entityType}-${entityId}][entity_type]" value="${entityType}">
            <input type="hidden" name="links[${entityType}-${entityId}][entity_id]" value="${entityId}">
        `;
        const newRow = `
            <tr>
                <td>${branchText}</td>
                <td><span class="badge bg-secondary-lt">${entityTypeNames[entityType] || entityType}</span></td>
                <td>${entityText} ${hiddenInputs}</td>
                <td><a href="#" class="btn btn-sm btn-ghost-danger delete-temp-link-btn">حذف</a></td>
            </tr>
        `;
        $linkedTableBody.append(newRow);
    });

    $linkedTableBody.on('click', '.delete-temp-link-btn', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        if ($linkedTableBody.find('tr').length === 1 && $linkedTableBody.find('#no-links-row').length === 1) { // If only the 'no links' row is left
             // This condition might need adjustment if the row is not just hidden but removed
        } else if ($linkedTableBody.find('tr').not('#no-links-row').length === 0) {
            $noLinksRow.show();
        }
    });
});
</script>