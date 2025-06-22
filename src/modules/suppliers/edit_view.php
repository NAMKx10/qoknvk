<?php
if (!isset($_GET['id'])) { die("ID is required."); }
$supplier_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();
if (!$supplier) { die("Supplier not found."); }

// جلب قائمة الفروع النشطة للاختيار
$branches_stmt = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'نشط' ORDER BY branch_name ASC");
$branches_list = $branches_stmt->fetchAll();

// جلب الفروع المرتبطة حاليًا بهذا المورد
$current_branches_stmt = $pdo->prepare("SELECT branch_id FROM supplier_branches WHERE supplier_id = ?");
$current_branches_stmt->execute([$supplier_id]);
$current_branch_ids = $current_branches_stmt->fetchAll(PDO::FETCH_COLUMN);

?>

<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>

<form method="POST" action="index.php?page=suppliers/handle_edit_ajax" class="ajax-form">
    <input type="hidden" name="id" value="<?php echo $supplier['id']; ?>">
    <div class="row g-3">
        <div class="col-sm-6">
            <label for="supplier_name" class="form-label">اسم المورد</label>
            <input type="text" class="form-control" id="supplier_name" name="supplier_name" value="<?php echo htmlspecialchars($supplier['supplier_name']); ?>" required>
        </div>
        <div class="col-sm-6">
            <label for="supplier_type" class="form-label">نوع المورد</label>
            <select class="form-select" id="supplier_type" name="supplier_type">
           <option value="منشأة" <?php echo ($supplier['supplier_type'] == 'منشأة') ? 'selected' : ''; ?>>منشأة</option>
            <option value="فرد" <?php echo ($supplier['supplier_type'] == 'فرد') ? 'selected' : ''; ?>>فرد</option>
            </select>
        </div>
            <div class="col-12">
                <label for="branches" class="form-label">الفروع المرتبطة (اختياري)</label>
                <select class="form-select select2-init" id="branches" name="branches[]" multiple data-placeholder="اختر فرعًا أو أكثر...">
                    <?php foreach ($branches_list as $branch): ?>
                        <option value="<?php echo $branch['id']; ?>" <?php echo in_array($branch['id'], $current_branch_ids) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($branch['branch_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <div class="col-sm-6">
            <label for="service_type" class="form-label">الخدمة المقدمة</label>
            <input type="text" class="form-control" id="service_type" name="service_type" value="<?php echo htmlspecialchars($supplier['service_type']); ?>">
        </div>
        <div class="col-sm-6">
            <label for="registration_number" class="form-label">رقم السجل التجاري</label>
            <input type="text" class="form-control" id="registration_number" name="registration_number" value="<?php echo htmlspecialchars($supplier['registration_number']); ?>">
        </div>
        <div class="col-sm-6">
            <label for="tax_number" class="form-label">الرقم الضريبي</label>
            <input type="text" class="form-control" id="tax_number" name="tax_number" value="<?php echo htmlspecialchars($supplier['tax_number']); ?>">
        </div>
        <div class="col-sm-6">
            <label for="contact_person" class="form-label">مسؤول التواصل</label>
            <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($supplier['contact_person']); ?>">
        </div>
        <div class="col-sm-6">
            <label for="mobile" class="form-label">الجوال</label>
            <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($supplier['mobile']); ?>">
        </div>
        <div class="col-sm-6">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($supplier['email']); ?>">
        </div>
        <div class="col-sm-6">
            <label for="status" class="form-label">الحالة</label>
            <select class="form-select" id="status" name="status">
                <option value="نشط" <?php echo ($supplier['status'] == 'نشط') ? 'selected' : ''; ?>>نشط</option>
                <option value="ملغي" <?php echo ($supplier['status'] == 'ملغي') ? 'selected' : ''; ?>>ملغي</option>
            </select>
        </div>
        <div class="col-12">
            <label for="address" class="form-label">العنوان</label>
            <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($supplier['address']); ?></textarea>
        </div>
        </div>
        <div class="col-12">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo htmlspecialchars($supplier['notes'] ?? ''); ?></textarea>
        </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>
