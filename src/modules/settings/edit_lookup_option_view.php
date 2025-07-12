<?php

// src/modules/settings/edit_lookup_option_view.php (النسخة الكاملة مع الإعدادات المتقدمة)

// --- 1. جلب البيانات الأساسية (يبقى كما هو) ---
if (!isset($_GET['id'])) { die("ID is required."); }
$stmt = $pdo->prepare("SELECT * FROM lookup_options WHERE id = ?");
$stmt->execute([$_GET['id']]);
$option = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$option) { die("Option not found."); }

// --- 2. فك ترميز المخططات (القديم والجديد) ---
$custom_fields = json_decode($option['custom_fields_schema'] ?? '[]', true);
$advanced_config = json_decode($option['advanced_config'] ?? '[]', true); // ✨ نقرأ الإعدادات الجديدة من قاعدة البيانات

?>

<!-- =============================================== -->
<!-- HTML: نموذج التعديل المتقدم                     -->
<!-- =============================================== -->


<div class="modal-header">
    <h5 class="modal-title">تعديل الخيار: <?= htmlspecialchars($option['option_value']) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form method="POST" action="index.php?page=settings/handle_edit_lookup_option_ajax" class="ajax-form">
    <input type="hidden" name="id" value="<?= $option['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>

        <!-- القسم الأول: البيانات الأساسية للخيار -->
        <fieldset class="form-fieldset">
            <legend>البيانات الأساسية</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="option_value" class="form-label">القيمة المعروضة</label>
                    <input type="text" class="form-control" name="option_value" value="<?= htmlspecialchars($option['option_value']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="option_key" class="form-label">المفتاح (انجليزي)</label>
                    <input type="text" class="form-control" name="option_key" value="<?= htmlspecialchars($option['option_key']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="bg_color" class="form-label">لون الخلفية</label>
                    <input type="color" class="form-control form-control-color" name="bg_color" value="<?= htmlspecialchars($option['bg_color'] ?? '#6c757d') ?>">
                </div>
                <div class="col-md-6">
                    <label for="color" class="form-label">لون النص</label>
                    <input type="color" class="form-control form-control-color" name="color" value="<?= htmlspecialchars($option['color'] ?? '#ffffff') ?>">
                </div>
            </div>
        </fieldset>

        <?php
        // --- القسم الثاني: يظهر فقط لأنواع الوثائق ---
        if ($option['group_key'] === 'documents_type'):
        ?>
            <fieldset class="form-fieldset mt-4">
                <legend>إدارة الحقول المخصصة</legend>
                <div id="custom-fields-container">
                    <!-- الحقول الحالية سيتم عرضها هنا بواسطة PHP -->
                    <?php foreach ($custom_fields as $index => $field): ?>
                        <div class="row g-2 mb-2 border p-2 rounded custom-field-row">
                            <div class="col"><input type="text" class="form-control" name="custom_fields[<?= $index ?>][label]" placeholder="اسم الحقل (للعرض)" value="<?= htmlspecialchars($field['label']) ?>"></div>
                            <div class="col"><input type="text" class="form-control" name="custom_fields[<?= $index ?>][name]" placeholder="الاسم البرمجي (انجليزي)" value="<?= htmlspecialchars($field['name']) ?>"></div>
                            <div class="col-auto"><select class="form-select" name="custom_fields[<?= $index ?>][type]"><option value="text" <?= $field['type'] == 'text' ? 'selected' : '' ?>>نص</option><option value="number" <?= $field['type'] == 'number' ? 'selected' : '' ?>>رقم</option><option value="date" <?= $field['type'] == 'date' ? 'selected' : '' ?>>تاريخ</option></select></div>
                            <div class="col-auto"><label class="form-check"><input class="form-check-input" type="checkbox" name="custom_fields[<?= $index ?>][required]" value="1" <?= isset($field['required']) && $field['required'] ? 'checked' : '' ?>><span class="form-check-label">مطلوب؟</span></label></div>
                            <div class="col-auto"><button type="button" class="btn btn-danger btn-icon" onclick="removeCustomField(this)"><i class="ti ti-trash"></i></button></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-outline-primary mt-2" id="add-custom-field-btn">
                    <i class="ti ti-plus me-2"></i>إضافة حقل مخصص
                </button>
            </fieldset>

<!-- ✨ القسم الثالث: هنا الجزء الجديد بالكامل ✨ -->
            <fieldset class="form-fieldset mt-4">
                <legend>إعدادات الربط المتقدم</legend>
                <div class="mb-3">
                    <label class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="advanced_config[has_expiration_date]" value="1" <?= isset($advanced_config['has_expiration_date']) && $advanced_config['has_expiration_date'] ? 'checked' : '' ?>>
                        <span class="form-check-label">هذه الوثيقة لها تاريخ انتهاء</span>
                    </label>
                    <div class="form-text">عند تفعيلها، سيظهر حقل "تاريخ الانتهاء" في شاشة الوثائق.</div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                         <label class="form-label">خيارات ربط الكيانات</label>
                         <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="advanced_config[link_one_property]" value="1" <?= isset($advanced_config['link_one_property']) && $advanced_config['link_one_property'] ? 'checked' : '' ?>>
                            <span class="form-check-label">السماح بربط عقار واحد</span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="advanced_config[link_many_owners]" value="1" <?= isset($advanced_config['link_many_owners']) && $advanced_config['link_many_owners'] ? 'checked' : '' ?>>
                            <span class="form-check-label">السماح بربط عدة ملاك</span>
                        </label>
                         <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="advanced_config[link_one_client]" value="1" <?= isset($advanced_config['link_one_client']) && $advanced_config['link_one_client'] ? 'checked' : '' ?>>
                            <span class="form-check-label">السماح بربط عميل واحد</span>
                        </label>
                    </div>
                     <div class="col-md-6">
                        <label class="form-label">خيارات إضافية</label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="advanced_config[show_percentage]" value="1" <?= isset($advanced_config['show_percentage']) && $advanced_config['show_percentage'] ? 'checked' : '' ?>>
                            <span class="form-check-label">تفعيل حقل نسبة الملكية</span>
                        </label>
                     </div>
                </div>
            </fieldset>

            <!-- قالب لإضافة حقل جديد عبر JavaScript (مخفي) -->
            <template id="custom-field-template">
                <div class="row g-2 mb-2 border p-2 rounded custom-field-row">
                    <div class="col"><input type="text" class="form-control" name="custom_fields[NEW_INDEX][label]" placeholder="اسم الحقل (للعرض)"></div>
                    <div class="col"><input type="text" class="form-control" name="custom_fields[NEW_INDEX][name]" placeholder="الاسم البرمجي (انجليزي)"></div>
                    <div class="col-auto"><select class="form-select" name="custom_fields[NEW_INDEX][type]"><option value="text">نص</option><option value="number">رقم</option><option value="date">تاريخ</option></select></div>
                    <div class="col-auto"><label class="form-check"><input class="form-check-input" type="checkbox" name="custom_fields[NEW_INDEX][required]" value="1"><span class="form-check-label">مطلوب؟</span></label></div>
                    <div class="col-auto"><button type="button" class="btn btn-danger btn-icon" onclick="removeCustomField(this)"><i class="ti ti-trash"></i></button></div>
                </div>
            </template>
        <?php endif; ?>
        
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>

<!-- =============================================== -->
<!-- JavaScript: منطق مصمم النماذج                   -->
<!-- =============================================== -->
<script>
$(document).ready(function() {
    let fieldIndex = <?= count($custom_fields) ?>;

    $('#add-custom-field-btn').on('click', function() {
        const template = document.getElementById('custom-field-template').content.cloneNode(true);
        const newRow = template.firstElementChild;
        newRow.innerHTML = newRow.innerHTML.replace(/NEW_INDEX/g, fieldIndex);
        document.getElementById('custom-fields-container').appendChild(newRow);
        fieldIndex++;
    });
});

function removeCustomField(button) {
    button.closest('.custom-field-row').remove();
}
</script>