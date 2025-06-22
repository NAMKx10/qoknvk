<?php
// src/modules/owners/edit_view.php
if (!isset($_GET['id'])) { die("ID is required."); }
$owner_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM owners WHERE id = ?");
$stmt->execute([$owner_id]);
$owner = $stmt->fetch();
if (!$owner) { die("Owner not found."); }
?>
<div class="modal-header">
    <h5 class="modal-title">تعديل المالك: <?= htmlspecialchars($owner['owner_name']) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=owners/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $owner['id'] ?>">
    <div class="modal-body">
         <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label required">اسم المالك</label><input type="text" class="form-control" name="owner_name" value="<?= htmlspecialchars($owner['owner_name']) ?>" required></div>
            <div class="col-md-6"><label class="form-label required">نوع المالك</label><select class="form-select" name="owner_type"><option value="فرد" <?= ($owner['owner_type'] == 'فرد')?'selected':'' ?>>فرد</option><option value="منشأة" <?= ($owner['owner_type'] == 'منشأة')?'selected':'' ?>>منشأة</option></select></div>
            <div class="col-md-6"><label class="form-label">كود المالك</label><input type="text" class="form-control" name="owner_code" value="<?= htmlspecialchars($owner['owner_code']) ?>"></div>
            <div class="col-md-6"><label class="form-label">رقم الهوية/السجل</label><input type="text" class="form-control" name="id_number" value="<?= htmlspecialchars($owner['id_number']) ?>"></div>
            <div class="col-md-6"><label class="form-label">الجوال</label><input type="text" class="form-control" name="mobile" value="<?= htmlspecialchars($owner['mobile']) ?>"></div>
            <div class="col-md-6"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($owner['email']) ?>"></div>
            <div class="col-md-6"><label class="form-label">الحالة</label><select class="form-select" name="status"><option value="نشط" <?= ($owner['status'] == 'نشط')?'selected':'' ?>>نشط</option><option value="ملغي" <?= ($owner['status'] == 'ملغي')?'selected':'' ?>>ملغي</option></select></div>
            <div class="col-12"><label class="form-label">ملاحظات</label><textarea class="form-control" name="notes" rows="2"><?= htmlspecialchars($owner['notes']) ?></textarea></div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>