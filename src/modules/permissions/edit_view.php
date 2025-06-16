<?php
if (!isset($_GET['id'])) { die("ID is required."); }
$stmt = $pdo->prepare("SELECT * FROM permissions WHERE id = ?");
$stmt->execute([$_GET['id']]);
$permission = $stmt->fetch();
if (!$permission) { die("Permission not found."); }
?>

<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>

<form method="POST" action="index.php?page=permissions/handle_edit_ajax" class="ajax-form">
    <input type="hidden" name="id" value="<?php echo $permission['id']; ?>">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="description" class="form-label">الوصف</label>
            <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($permission['description'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="permission_key" class="form-label">المفتاح (انجليزي، بدون مسافات)</label>
            <input type="text" class="form-control" id="permission_key" name="permission_key" value="<?php echo htmlspecialchars($permission['permission_key'] ?? ''); ?>" required>
        </div>
    </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>