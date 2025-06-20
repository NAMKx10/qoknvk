<?php
$group_key = $_GET['group'] ?? '';
if (empty($group_key)) { die("Group key is required."); }
$stmt = $pdo->prepare("SELECT option_value FROM lookup_options WHERE group_key = ? AND option_key = ?");
$stmt->execute([$group_key, $group_key]);
$group_display_name = $stmt->fetchColumn();
?>
<div class="modal-header">
<h5 class="modal-title">تعديل المجموعة</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=settings/handle_edit_lookup_group_ajax" class="ajax-form">
<input type="hidden" name="original_group_key" value="<?= htmlspecialchars($group_key) ?>">
<div class="modal-body">
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<div class="mb-3">
<label class="form-label">اسم المجموعة الجديد (للعرض)</label>
<input type="text" class="form-control" name="new_option_value" value="<?= htmlspecialchars($group_display_name ?: $group_key) ?>" required>
</div>
<div class="mb-3">
<label class="form-label">مفتاح المجموعة الجديد (انجليزي، بدون مسافات)</label>
<input type="text" class="form-control" name="new_group_key" value="<?= htmlspecialchars($group_key) ?>" required>
</div>
</div>
<div class="modal-footer">
<a href="#" class="btn" data-bs-dismiss="modal">إلغاء</a>
<button type="submit" class="btn btn-primary">حفظ التعديلات</button>
</div>
</form>