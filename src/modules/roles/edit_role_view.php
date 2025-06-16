<?php
if (!isset($_GET['id'])) { die("ID is required."); }
$stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$_GET['id']]);
$role = $stmt->fetch();
if (!$role) { die("Role not found."); }
?>
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=roles/handle_edit_role_ajax" class="ajax-form">
    <input type="hidden" name="id" value="<?php echo $role['id']; ?>">
    <div class="mb-3"><label for="role_name" class="form-label">اسم الدور</label><input type="text" class="form-control" id="role_name" name="role_name" value="<?php echo htmlspecialchars($role['role_name']); ?>" required></div>
    <div class="mb-3"><label for="description" class="form-label">الوصف</label><textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($role['description']); ?></textarea></div>
    <hr><div class="d-flex justify-content-end"><button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ التعديلات</button></div>
</form>