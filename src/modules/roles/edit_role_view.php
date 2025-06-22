<?php
// src/modules/roles/edit_role_view.php
if (!isset($_GET['id'])) { die("ID is required."); }
$stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$_GET['id']]);
$role = $stmt->fetch();
if (!$role) { die("Role not found."); }
?>
<div class="modal-header">
    <h5 class="modal-title">تعديل بيانات الدور</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form method="POST" action="index.php?page=roles/handle_edit_role" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <input type="hidden" name="id" value="<?= $role['id'] ?>">
        <div class="mb-3">
            <label class="form-label required">اسم الدور</label>
            <input type="text" class="form-control" name="role_name" value="<?= htmlspecialchars($role['role_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">الوصف</label>
            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($role['description']) ?></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>