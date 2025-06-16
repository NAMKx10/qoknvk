<?php
if (!isset($_GET['id'])) { die("ID is required."); }
$stmt = $pdo->prepare("SELECT * FROM permission_groups WHERE id = ?");
$stmt->execute([$_GET['id']]);
$group = $stmt->fetch();
if (!$group) { die("Group not found."); }
?>
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=permissions/handle_edit_group_ajax" class="ajax-form">
    <input type="hidden" name="id" value="<?php echo $group['id']; ?>">
    <div class="mb-3">
        <label for="group_name" class="form-label">اسم المجموعة (للعرض)</label>
        <input type="text" class="form-control" id="group_name" name="group_name" value="<?php echo htmlspecialchars($group['group_name']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="group_key" class="form-label">مفتاح المجموعة (انجليزي، فريد)</label>
        <input type="text" class="form-control" id="group_key" name="group_key" value="<?php echo htmlspecialchars($group['group_key']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">وصف المجموعة</label>
        <textarea class="form-control" id="description" name="description" rows="2"><?php echo htmlspecialchars($group['description']); ?></textarea>
    </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>