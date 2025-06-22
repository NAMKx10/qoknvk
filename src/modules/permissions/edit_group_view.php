<?php
// src/modules/permissions/edit_group_view.php

if (!isset($_GET['id'])) { die("ID is required."); }
$group_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM permission_groups WHERE id = ?");
$stmt->execute([$group_id]);
$group = $stmt->fetch();
if (!$group) { die("Group not found."); }
?>

<div class="modal-header">
    <h5 class="modal-title">تعديل المجموعة: <?= htmlspecialchars($group['group_name']) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=permissions/handle_edit_group" class="ajax-form">
    <input type="hidden" name="id" value="<?= $group['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="mb-3">
            <label class="form-label required">اسم المجموعة</label>
            <input type="text" class="form-control" name="group_name" value="<?= htmlspecialchars($group['group_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">ترتيب العرض</label>
            <input type="number" class="form-control" name="display_order" value="<?= htmlspecialchars($group['display_order']) ?>">
            <small class="form-hint">يستخدم لترتيب المجموعات في القائمة. الأرقام الأقل تظهر أولاً.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">الوصف (اختياري)</label>
            <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($group['description']) ?></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>