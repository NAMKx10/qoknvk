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
        <!-- (جديد) قسم إدارة الروابط - سيتم بناؤه هنا لاحقاً -->
        <!-- ============================================= -->
        
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>