<?php
// src/modules/documents/edit_view.php (الإصدار النهائي مع آلية الربط الكاملة)

// --- 1. جلب البيانات ---
if (!isset($_GET['id'])) { die("ID is required."); }
$doc_id = $_GET['id'];

// جلب بيانات الوثيقة
$doc_stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
$doc_stmt->execute([$doc_id]);
$document = $doc_stmt->fetch(PDO::FETCH_ASSOC);
if (!$document) { die("Document not found."); }

// جلب أنواع الوثائق والبيانات المخصصة
$types_map = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'documents_type'")->fetchAll(PDO::FETCH_KEY_PAIR);
$schema_stmt = $pdo->prepare("SELECT custom_fields_schema FROM lookup_options WHERE group_key = 'documents_type' AND option_key = ?");
$schema_stmt->execute([$document['document_type']]);
$custom_fields_schema = json_decode($schema_stmt->fetchColumn() ?: '[]', true);
$details = json_decode($document['details'] ?: '[]', true);

// جلب الحالات والفروع
$statuses = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'status' AND option_key != 'status' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_ASSOC);
$branches = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="modal-header">
    <h5 class="modal-title">تعديل الوثيقة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form method="POST" action="index.php?page=documents/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $document['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        
        <!-- البيانات الأساسية -->
        <fieldset class="form-fieldset"><div class="row g-3">
            <div class="col-md-6"><label class="form-label">نوع الوثيقة</label><input type="text" class="form-control" value="<?= htmlspecialchars($types_map[$document['document_type']] ?? $document['document_type']) ?>" readonly></div>
            <div class="col-md-6"><label class="form-label">اسم الوثيقة</label><input type="text" class="form-control" name="document_name" value="<?= htmlspecialchars($document['document_name']) ?>"></div>
            <div class="col-md-6"><label class="form-label">رقم الوثيقة</label><input type="text" class="form-control" name="document_number" value="<?= htmlspecialchars($document['document_number']) ?>"></div>
            <div class="col-md-6"><label class="form-label">الحالة</label><select class="form-select" name="status"><?php foreach($statuses as $status):?><option value="<?= htmlspecialchars($status['option_key']) ?>" <?= ($document['status'] == $status['option_key']) ? 'selected' : '' ?>><?= htmlspecialchars($status['option_value']) ?></option><?php endforeach; ?></select></div>
                            <div class="col-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($document['notes']) ?></textarea>
                </div>

            <div class="col-md-6"><label class="form-label">تاريخ الإصدار</label><input type="date" class="form-control" name="issue_date" value="<?= htmlspecialchars($document['issue_date']) ?>"></div>
            <div class="col-md-6"><label class="form-label">تاريخ الانتهاء</label><input type="date" class="form-control" name="expiry_date" value="<?= htmlspecialchars($document['expiry_date']) ?>"></div>
        </div></fieldset>
        
        <!-- الحقول المخصصة -->
        <?php if (!empty($custom_fields_schema)): ?>
            <fieldset class="form-fieldset mt-3"><legend>تفاصيل إضافية</legend><div class="row g-3">
            <?php foreach($custom_fields_schema as $field): ?>
                <div class="col-md-6"><label class="form-label"><?= htmlspecialchars($field['label']) ?><?= ($field['required'] ?? false) ? '<span class="text-danger">*</span>' : '' ?></label><input type="<?= htmlspecialchars($field['type']) ?>" class="form-control" name="details[<?= htmlspecialchars($field['name']) ?>]" value="<?= htmlspecialchars($details[$field['name']] ?? '') ?>" <?= ($field['required'] ?? false) ? 'required' : '' ?>></div>
            <?php endforeach; ?>
            </div></fieldset>
        <?php endif; ?>

        <!-- إدارة الروابط -->
        <fieldset class="form-fieldset mt-3">
            <legend>الكيانات المرتبطة</legend>
            <div id="linked-entities-table" class="mb-3"></div>
            <div class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label">1. اختر الفرع (اختياري)</label><select class="form-select" id="branch-select-filter"><option value="">-- كل الفروع --</option><?php foreach($branches as $branch): ?><option value="<?= $branch['id'] ?>"><?= htmlspecialchars($branch['branch_name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">2. نوع الكيان</label><select class="form-select" id="entity-type-select"><option value="">-- اختر --</option><option value="property">عقار</option><option value="owner">مالك</option><option value="client">عميل</option><option value="supplier">مورد</option></select></div>
                <div class="col-md-4"><label class="form-label">3. اختر الكيان</label><select class="form-select" id="entity-id-select" disabled></select></div>
                <div class="col-auto"><button type="button" class="btn btn-primary" id="add-link-btn" disabled>إضافة رابط</button></div>
            </div>
        </fieldset>
    </div>
    <div class="modal-footer"><button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ التعديلات</button></div>
</form>

<!-- الكود الكامل للجافاسكريبت -->
<script>
$(document).ready(function() {
    const docId = <?= $doc_id ?>;
    const $entityTypeSelect = $('#entity-type-select');
    const $entityIdSelect = $('#entity-id-select');
    const $addLinkBtn = $('#add-link-btn');
    const $linkedEntitiesTable = $('#linked-entities-table');
    const $branchFilter = $('#branch-select-filter');

    function initializeEntitySelect() {
        $entityIdSelect.select2({
            theme: "bootstrap-5",
            dir: "rtl",
            placeholder: "ابحث أو اختر...",
            dropdownParent: $entityIdSelect.closest('.modal')
        });
    }

    function loadLinkedEntities() {
        $linkedEntitiesTable.html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm"></div></div>');
        $.get(`index.php?page=documents/get_linked_entities_ajax&doc_id=${docId}`, function(data) {
            $linkedEntitiesTable.html(data);
        });
    }

    loadLinkedEntities();
    initializeEntitySelect();

    function fetchEntities() {
        const entityType = $entityTypeSelect.val();
        const branchId = $branchFilter.val();
        
        $entityIdSelect.empty().prop('disabled', true);
        $addLinkBtn.prop('disabled', true);

        if (!entityType) return;

        $.ajax({
            url: 'index.php?page=documents/get_entities_for_linking_ajax',
            type: 'GET',
            data: { type: entityType, branch_id: branchId },
            dataType: 'json',
            success: function(entities) {
                $entityIdSelect.append(new Option('', ''));
                entities.forEach(function(entity) {
                    $entityIdSelect.append(new Option(entity.text, entity.id));
                });
                $entityIdSelect.prop('disabled', false);
                $addLinkBtn.prop('disabled', false);
                $entityIdSelect.val(null).trigger('change');
            }
        });
    }
    
    $entityTypeSelect.on('change', fetchEntities);
    $branchFilter.on('change', fetchEntities);

    $addLinkBtn.on('click', function() {
        const entityType = $entityTypeSelect.val();
        const entityId = $entityIdSelect.val();

        if (!entityType || !entityId) {
            alert('يرجى اختيار نوع وكيان للربط.');
            return;
        }

        $.post('index.php?page=documents/add_link_ajax', { doc_id: docId, entity_type: entityType, entity_id: entityId }, function(response) {
            if (response.success) {
                loadLinkedEntities();
                $entityIdSelect.val(null).trigger('change');
            } else { alert(response.message || 'حدث خطأ.'); }
        }, 'json');
    });
    
    $('body').on('click', '.delete-link-btn', function(e) {
        e.preventDefault();
        const linkId = $(this).data('link-id');
        if (confirm('هل أنت متأكد من حذف هذا الرابط؟')) {
             $.post('index.php?page=documents/delete_link_ajax', { link_id: linkId }, function(response) {
                if (response.success) {
                    loadLinkedEntities();
                } else { alert(response.message || 'حدث خطأ.'); }
            }, 'json');
        }
    });
});
</script>