<?php
// src/modules/permissions/edit_view.php

if (!isset($_GET['id'])) { die("ID is required."); }
$permission_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM permissions WHERE id = ?");
$stmt->execute([$permission_id]);
$permission = $stmt->fetch();
if (!$permission) { die("Permission not found."); }
?>
<div class="modal-header">
    <h5 class="modal-title">تعديل الصلاحية</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form method="POST" action="index.php?page=permissions/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $permission['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="mb-3">
            <label class="form-label required">الوصف</label>
            <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($permission['description']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label required">المفتاح البرمجي</label>
            <input type="text" class="form-control" name="permission_key" value="<?= htmlspecialchars($permission['permission_key']) ?>" required>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>