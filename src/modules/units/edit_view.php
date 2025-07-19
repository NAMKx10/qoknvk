<?php
// src/modules/units/edit_view.php (النسخة النهائية الكاملة والمصححة)

global $pdo;

if (!isset($_GET['id'])) { die("ID is required."); }
$unit_id = (int)$_GET['id'];

// جلب بيانات الوحدة المحدد
$stmt = $pdo->prepare("SELECT * FROM units WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$unit_id]);
$unit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$unit) {
    http_response_code(404);
    die("لم يتم العثور على الوحدة.");
}

// بناء جملة WHERE مع فلترة الفروع لضمان أمان القائمة المنسدلة
$sql_where = " WHERE p.deleted_at IS NULL AND p.status = 'Active' ";
$params = [];
$sql_where .= build_branches_query_condition('p', $params);

$properties_sql = "SELECT p.id, p.property_name FROM properties p {$sql_where} ORDER BY p.property_name ASC";
$properties_stmt = $pdo->prepare($properties_sql);
$properties_stmt->execute($params);
$properties_list = $properties_stmt->fetchAll();

// جلب الخيارات من تهيئة المدخلات
$unit_types = get_lookup_options($pdo, 'unit_type');
$statuses = get_lookup_options($pdo, 'status', true);
?>

<div class="modal-header">
    <h5 class="modal-title">تعديل الوحدة: <?= htmlspecialchars($unit['unit_name']) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form method="POST" action="index.php?page=units/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $unit['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-sm-6">
                <label class="form-label required">العقار</label>
                <select class="form-select select2-init" name="property_id" required data-placeholder="اختر...">
                    <?php foreach ($properties_list as $p): ?>
                        <option value="<?=$p['id']?>" <?= ($unit['property_id'] == $p['id']) ? 'selected' : '' ?>><?=htmlspecialchars($p['property_name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6">
                <label class="form-label required">اسم الوحدة</label>
                <input type="text" class="form-control" name="unit_name" value="<?= htmlspecialchars($unit['unit_name']) ?>" required>
            </div>
            <div class="col-sm-6">
                <label class="form-label">رقم الوحدة</label>
                <input type="text" class="form-control" name="unit_number" value="<?= htmlspecialchars($unit['unit_number'] ?? '') ?>">
            </div>
            <div class="col-sm-6">
                <label class="form-label">كود الوحدة</label>
                <input type="text" class="form-control" name="unit_code" value="<?= htmlspecialchars($unit['unit_code']) ?>">
            </div>
            <div class="col-sm-6">
                <label class="form-label">نوع الوحدة</label>
                <select class="form-select select2-init" name="unit_type">
                    <option value="">اختر...</option>
                    <?php foreach($unit_types as $type): ?>
                        <option value="<?=htmlspecialchars($type)?>" <?=($unit['unit_type'] == $type) ? 'selected' : ''?>><?=htmlspecialchars($type)?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="col-sm-6">
                <label class="form-label">المساحة (م²)</label>
                <input type="number" step="0.01" class="form-control" name="area" value="<?= htmlspecialchars($unit['area']) ?>">
            </div>
            <div class="col-sm-6">
                <label class="form-label">الدور</label>
                <input type="number" class="form-control" name="floor" value="<?= htmlspecialchars($unit['floor']) ?>">
            </div>
            <div class="col-sm-6">
                <label class="form-label">الحالة</label>
                <select class="form-select" name="status">
                    <?php foreach ($statuses as $key => $value): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= ($unit['status'] === $value) ? 'selected' : '' ?>><?= htmlspecialchars($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">ملاحظات</label>
                <textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($unit['notes']) ?></textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>