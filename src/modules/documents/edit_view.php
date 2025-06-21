<?php
// src/modules/documents/edit_view.php

// --- 1. جلب البيانات ---
if (!isset($_GET['id'])) { die("ID is required."); }
$doc_id = $_GET['id'];

// جلب بيانات الوثيقة الرئيسية
$doc_stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
$doc_stmt->execute([$doc_id]);
$document = $doc_stmt->fetch(PDO::FETCH_ASSOC);
if (!$document) { die("Document not found."); }

// جلب مخطط الحقول المخصصة لهذا النوع من الوثائق
$schema_stmt = $pdo->prepare("SELECT custom_fields_schema FROM lookup_options WHERE group_key = 'documents_type' AND option_key = ?");
$schema_stmt->execute([$document['document_type']]);
$schema_json = $schema_stmt->fetchColumn();
$custom_fields_schema = json_decode($schema_json ?: '[]', true);

// فك ترميز البيانات المخصصة المحفوظة
$details = json_decode($document['details'] ?: '[]', true);

$statuses = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'status' AND option_key != 'status' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_ASSOC);


?>

<div class="modal-header">
    <h5 class="modal-title">تعديل الوثيقة</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form method="POST" action="index.php?page=documents/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $document['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        
        <!-- الحقول الأساسية -->
        <fieldset class="form-fieldset">
            <legend>البيانات الأساسية</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">نوع الوثيقة</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($document['document_type']) ?>" readonly disabled>
                </div>
                    <div class="col-md-6">
        <label class="form-label">اسم الوثيقة (اختياري)</label>
        <input type="text" class="form-control" name="document_name" value="<?= htmlspecialchars($document['document_name']) ?>">
    </div>

                <div class="col-md-6">
                    <label class="form-label">رقم الوثيقة</label>
                    <input type="text" class="form-control" name="document_number" value="<?= htmlspecialchars($document['document_number']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">تاريخ الإصدار</label>
                    <input type="date" class="form-control" name="issue_date" value="<?= htmlspecialchars($document['issue_date']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">تاريخ الانتهاء</label>
                    <input type="date" class="form-control" name="expiry_date" value="<?= htmlspecialchars($document['expiry_date']) ?>">
                </div>
                    <div class="col-md-6">
                      <label class="form-label">الحالة</label>
                         <select class="form-select" name="status">
                        <?php foreach($statuses as $status): ?>
                        <option value="<?= htmlspecialchars($status['option_key']) ?>" <?= ($document['status'] == $status['option_key']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($status['option_value']) ?>
                        </option>
                        <?php endforeach; ?>
                       </select>
                    </div>
                </div>

            </div>
        </fieldset>
        
        <!-- الحقول المخصصة (إذا كانت موجودة) -->
        <?php if (!empty($custom_fields_schema)): ?>
            <fieldset class="form-fieldset mt-4">
                <legend>تفاصيل إضافية</legend>
                <div class="row g-3">
                    <?php foreach($custom_fields_schema as $field): ?>
                        <div class="col-md-6">
                            <label class="form-label"><?= htmlspecialchars($field['label']) ?></label>
                            <input 
                                type="<?= htmlspecialchars($field['type']) ?>" 
                                class="form-control" 
                                name="details[<?= htmlspecialchars($field['name']) ?>]"
                                value="<?= htmlspecialchars($details[$field['name']] ?? '') ?>"
                                <?= ($field['required'] ?? false) ? 'required' : '' ?>>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endif; ?>

        <!-- ============================================= -->
        <!-- (جديد) قسم إدارة الروابط- -->
        <!-- ============================================= -->
                 <!-- (مُحسَّن) قسم عرض الحقول المخصصة -->
        <?php if (!empty($custom_fields_schema)): ?>
            <fieldset class="form-fieldset mt-4">
                <legend>تفاصيل إضافية</legend>
                <div class="row g-3">
                    <?php foreach($custom_fields_schema as $field): ?>
                        <div class="col-md-6">
                            <label class="form-label">
                                <?= htmlspecialchars($field['label']) ?>
                                <?= ($field['required'] ?? false) ? '<span class="text-danger">*</span>' : '' ?>
                            </label>
                            <input 
                                type="<?= htmlspecialchars($field['type']) ?>" 
                                class="form-control" 
                                name="details[<?= htmlspecialchars($field['name']) ?>]"
                                value="<?= htmlspecialchars($details[$field['name']] ?? '') ?>"
                                <?= ($field['required'] ?? false) ? 'required' : '' ?>>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endif; ?>


        <!-- (جديد) قسم إدارة الروابط التفاعلي -->
        <fieldset class="form-fieldset mt-4">
            <legend>الكيانات المرتبطة</legend>
            
            <!-- جدول لعرض الروابط الحالية -->
            <div id="linked-entities-table" class="mb-3">
                <!-- الروابط الحالية سيتم تحميلها هنا -->
            </div>

            <!-- نموذج إضافة رابط جديد -->
            <div class="row g-2 align-items-end">
                <div class="col">
                    <label class="form-label">نوع الكيان</label>
                    <select class="form-select" id="entity-type-select">
                        <option value="">-- اختر --</option>
                        <option value="property">عقار</option>
                        <option value="owner">مالك</option>
                        <option value="client">عميل</option>
                        <option value="supplier">مورد</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">اختر الكيان</label>
                    <select class="form-select" id="entity-id-select" disabled>
                        <!-- الخيارات سيتم تحميلها هنا -->
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary" id="add-link-btn" disabled>إضافة رابط</button>
                </div>
            </div>
        </fieldset>        
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>

<script>
$(document).ready(function() {
    const docId = <?= $doc_id ?>;
    const $entityTypeSelect = $('#entity-type-select');
    const $entityIdSelect = $('#entity-id-select');
    const $addLinkBtn = $('#add-link-btn');
    const $linkedEntitiesTable = $('#linked-entities-table');

    // 1. دالة لتفعيل Select2
    function initializeEntitySelect() {
        $entityIdSelect.select2({
            theme: "bootstrap-5",
            dir: "rtl",
            placeholder: "ابحث أو اختر...",
            dropdownParent: $entityIdSelect.closest('.modal')
        });
    }

    // 2. دالة لجلب وعرض الروابط الحالية
    function loadLinkedEntities() {
        $linkedEntitiesTable.html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm"></div></div>');
        $.get(`index.php?page=documents/get_linked_entities_ajax&doc_id=${docId}`, function(data) {
            $linkedEntitiesTable.html(data);
        });
    }

    // 3. جلب الروابط عند تحميل الصفحة
    loadLinkedEntities();
    initializeEntitySelect();

    // 4. عند تغيير نوع الكيان
    $entityTypeSelect.on('change', function() {
        const entityType = $(this).val();
        $entityIdSelect.empty().prop('disabled', true);
        $addLinkBtn.prop('disabled', true);

        if (!entityType) return;

        // جلب قائمة الكيانات
        $.ajax({
            url: 'index.php?page=documents/get_entities_for_linking_ajax',
            type: 'GET',
            data: { type: entityType },
            dataType: 'json',
            success: function(entities) {
                $entityIdSelect.append(new Option('', '')); // For placeholder
                entities.forEach(function(entity) {
                    $entityIdSelect.append(new Option(entity.text, entity.id));
                });
                $entityIdSelect.prop('disabled', false);
                $addLinkBtn.prop('disabled', false);
            }
        });
    });

    // 5. عند الضغط على زر "إضافة رابط"
    $addLinkBtn.on('click', function() {
        const entityType = $entityTypeSelect.val();
        const entityId = $entityIdSelect.val();

        if (!entityType || !entityId) {
            alert('يرجى اختيار نوع وكيان للربط.');
            return;
        }

        $.post('index.php?page=documents/add_link_ajax', { doc_id: docId, entity_type: entityType, entity_id: entityId }, function(response) {
            if (response.success) {
                loadLinkedEntities(); // إعادة تحميل الجدول
                $entityIdSelect.val(null).trigger('change'); // تفريغ القائمة
            } else {
                alert(response.message || 'حدث خطأ.');
            }
        }, 'json');
    });
    
    // 6. عند الضغط على زر "حذف الرابط" (يتم ربطه بالأزرار التي سيتم جلبها)
    $('body').on('click', '.delete-link-btn', function(e) {
        e.preventDefault();
        const linkId = $(this).data('link-id');
        if (confirm('هل أنت متأكد من حذف هذا الرابط؟')) {
             $.post('index.php?page=documents/delete_link_ajax', { link_id: linkId }, function(response) {
                if (response.success) {
                    loadLinkedEntities();
                } else {
                    alert(response.message || 'حدث خطأ.');
                }
            }, 'json');
        }
    });

});
</script>
