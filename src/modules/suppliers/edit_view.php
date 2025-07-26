<?php
// src/modules/suppliers/edit_view.php (النسخة المطورة)

if (!isset($_GET['id'])) { die("ID is required."); }
$supplier_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();
if (!$supplier) { die("Supplier not found."); }

// جلب البيانات الديناميكية
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'Active' AND deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();
$current_branch_ids = $pdo->prepare("SELECT branch_id FROM supplier_branches WHERE supplier_id = ?");
$current_branch_ids->execute([$supplier_id]);
$current_branch_ids = $current_branch_ids->fetchAll(PDO::FETCH_COLUMN);

$supplier_types = get_lookup_options($pdo, 'entity_type');
$statuses = get_lookup_options($pdo, 'status', true);
?>

<div class="modal-header">
    <h5 class="modal-title">تعديل المورد: <?= htmlspecialchars($supplier['supplier_name']) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form method="POST" action="index.php?page=suppliers/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $supplier['id']; ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم المورد</label><input type="text" class="form-control" name="supplier_name" value="<?= htmlspecialchars($supplier['supplier_name']); ?>" required></div>
            <div class="col-md-6"><label class="form-label">كود المورد</label><input type="text" class="form-control" name="supplier_code" value="<?= htmlspecialchars($supplier['supplier_code'] ?? ''); ?>"></div>
            <div class="col-md-6">
                <label class="form-label required">نوع المورد</label>
                <select class="form-select" name="supplier_type" required>
                    <?php foreach($supplier_types as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= ($supplier['supplier_type'] == $type) ? 'selected' : ''; ?>><?= htmlspecialchars($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
             <div class="col-md-6"><label class="form-label">الخدمة المقدمة</label><input type="text" class="form-control" name="service_type" value="<?= htmlspecialchars($supplier['service_type']); ?>"></div>
            <div class="col-md-6"><label class="form-label">رقم السجل</label><input type="text" class="form-control" name="registration_number" value="<?= htmlspecialchars($supplier['registration_number']); ?>"></div>
            <div class="col-md-6"><label class="form-label">الرقم الضريبي</label><input type="text" class="form-control" name="tax_number" value="<?= htmlspecialchars($supplier['tax_number']); ?>"></div>
            <div class="col-md-6"><label class="form-label">مسؤول التواصل</label><input type="text" class="form-control" name="contact_person" value="<?= htmlspecialchars($supplier['contact_person']); ?>"></div>
            <div class="col-md-6"><label class="form-label">الجوال</label><input type="text" class="form-control" name="mobile" value="<?= htmlspecialchars($supplier['mobile']); ?>"></div>
            <div class="col-md-6"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($supplier['email']); ?>"></div>
            <div class="col-md-6">
                <label class="form-label">الحالة</label>
                <select class="form-select" name="status">
                    <?php foreach($statuses as $key => $value): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= ($supplier['status'] == $key) ? 'selected' : ''; ?>><?= htmlspecialchars($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">الفروع المرتبطة</label>
                <select class="form-select select2-init" name="branches[]" multiple data-placeholder="اختر فرعًا أو أكثر...">
                    <?php foreach ($branches_list as $branch): ?>
                        <option value="<?= $branch['id']; ?>" <?= in_array($branch['id'], $current_branch_ids) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($branch['branch_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12"><label class="form-label">العنوان</label><textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($supplier['address']); ?></textarea></div>
            <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($supplier['notes'] ?? ''); ?></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>