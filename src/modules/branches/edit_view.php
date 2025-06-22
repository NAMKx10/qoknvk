<?php
// جلب بيانات الفرع للتعديل
if (!isset($_GET['id'])) { die("ID is required."); }
$branch_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM branches WHERE id = ?");
$stmt->execute([$branch_id]);
$branch = $stmt->fetch();
if (!$branch) { die("Branch not found."); }
?>

<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>

<form method="POST" action="index.php?page=branches/handle_edit_ajax" class="ajax-form">
    <input type="hidden" name="id" value="<?php echo $branch['id']; ?>">
    <div class="row g-3">
        <div class="col-sm-6"><label for="branch_name" class="form-label">اسم الفرع/الشركة</label><input type="text" class="form-control" id="branch_name" name="branch_name" value="<?php echo htmlspecialchars($branch['branch_name']); ?>" required></div>
        <div class="col-sm-6"><label for="branch_code" class="form-label">كود الفرع (فريد)</label><input type="text" class="form-control" id="branch_code" name="branch_code" value="<?php echo htmlspecialchars($branch['branch_code'] ?? ''); ?>"></div>
        <div class="col-sm-6"><label for="branch_type" class="form-label">نوع الكيان</label><select class="form-select" id="branch_type" name="branch_type"><option value="منشأة" <?php echo ($branch['branch_type'] == 'منشأة') ? 'selected' : ''; ?>>منشأة</option><option value="فرد" <?php echo ($branch['branch_type'] == 'فرد') ? 'selected' : ''; ?>>فرد</option></select></div>
        <div class="col-sm-6"><label for="registration_number" class="form-label">رقم السجل</label><input type="text" class="form-control" id="registration_number" name="registration_number" value="<?php echo htmlspecialchars($branch['registration_number'] ?? ''); ?>"></div>
        <div class="col-sm-6"><label for="tax_number" class="form-label">الرقم الضريبي</label><input type="text" class="form-control" id="tax_number" name="tax_number" value="<?php echo htmlspecialchars($branch['tax_number'] ?? ''); ?>"></div>
        <div class="col-sm-6"><label for="phone" class="form-label">الجوال/الهاتف</label><input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($branch['phone'] ?? ''); ?>"></div>
        <div class="col-sm-6"><label for="email" class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($branch['email'] ?? ''); ?>"></div>
        <div class="col-sm-6"><label for="status" class="form-label">الحالة</label><select class="form-select" id="status" name="status"><option value="نشط" <?php echo ($branch['status'] == 'نشط') ? 'selected' : ''; ?>>نشط</option><option value="ملغي" <?php echo ($branch['status'] == 'ملغي') ? 'selected' : ''; ?>>ملغي</option></select></div>
        <div class="col-12"><label for="address" class="form-label">العنوان</label><textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($branch['address'] ?? ''); ?></textarea></div>
        <div class="col-12"><label for="notes" class="form-label">ملاحظات</label><textarea class="form-control" id="notes" name="notes" rows="2"><?php echo htmlspecialchars($branch['notes'] ?? ''); ?></textarea></div>
    </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>