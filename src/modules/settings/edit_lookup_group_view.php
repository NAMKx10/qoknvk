<?php
$group_key = $_GET['group'] ?? '';
if (empty($group_key)) { die("Group key is required."); }

// جلب الاسم المعروض للمجموعة (إذا وجد)
$stmt = $pdo->prepare("SELECT option_value FROM lookup_options WHERE group_key = ? AND option_key = ?");
$stmt->execute([$group_key, $group_key]);
$group_display_name = $stmt->fetchColumn();
?>
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=settings/handle_edit_lookup_group_ajax" class="ajax-form">
    <input type="hidden" name="original_group_key" value="<?php echo htmlspecialchars($group_key); ?>">
    <div class="mb-3">
        <label for="new_option_value" class="form-label">اسم المجموعة الجديد (للعرض)</label>
        <input type="text" class="form-control" id="new_option_value" name="new_option_value" value="<?php echo htmlspecialchars($group_display_name ?: $group_key); ?>" required>
    </div>
    <div class="mb-3">
        <label for="new_group_key" class="form-label">مفتاح المجموعة الجديد (انجليزي، بدون مسافات)</label>
        <input type="text" class="form-control" id="new_group_key" name="new_group_key" value="<?php echo htmlspecialchars($group_key); ?>" required>
    </div>
    <hr>
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>