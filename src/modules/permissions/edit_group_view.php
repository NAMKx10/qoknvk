<?php if(!isset($_GET['id'])) die("ID required."); $group = $pdo->query("SELECT * FROM permission_groups WHERE id = ". (int)$_GET['id'])->fetch(); ?>
<div class="modal-header"><h5 class="modal-title">تعديل المجموعة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="index.php?page=permissions/handle_edit_group" class="ajax-form">
    <input type="hidden" name="id" value="<?= $group['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="mb-3"><label class="form-label required">اسم المجموعة</label><input type="text" class="form-control" name="group_name" value="<?= htmlspecialchars($group['group_name']) ?>" required></div>
        <div class="mb-3"><label class="form-label">الوصف</label><textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($group['description']) ?></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ التعديلات</button></div>
</form>