<?php
if (!isset($_GET['id'])) { die("ID is required."); }
$user_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) { die("User not found."); }
// === بداية الإضافة ===
// جلب قائمة كل الفروع النشطة للاختيار
$branches_stmt = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'نشط' ORDER BY branch_name ASC");
$branches_list = $branches_stmt->fetchAll();

// جلب الفروع المرتبطة حاليًا بهذا المستخدم
$current_branches_stmt = $pdo->prepare("SELECT branch_id FROM user_branches WHERE user_id = ?");
$current_branches_stmt->execute([$user_id]);
$current_branch_ids = $current_branches_stmt->fetchAll(PDO::FETCH_COLUMN);
// === نهاية الإضافة ===
$roles_stmt = $pdo->query("SELECT id, role_name FROM roles ORDER BY role_name ASC");
$roles_list = $roles_stmt->fetchAll();
?>
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=users/handle_edit_ajax" class="ajax-form">
    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
    <div class="row g-3">
        <div class="col-sm-6"><label for="full_name" class="form-label">الاسم الكامل</label><input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required></div>
        <div class="col-sm-6"><label for="username" class="form-label">اسم المستخدم (للدخول)</label><input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required></div>
        <div class="col-sm-6"><label for="email" class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"></div>
        <div class="col-sm-6"><label for="mobile" class="form-label">الجوال</label><input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>"></div>
        <div class="col-sm-6"><label for="password" class="form-label">كلمة المرور الجديدة (اتركه فارغاً لعدم التغيير)</label><input type="password" class="form-control" id="password" name="password"></div>
        <div class="col-sm-6"><label for="role_id" class="form-label">الدور (الصلاحية)</label><select class="form-select" id="role_id" name="role_id" required><?php foreach($roles_list as $role): ?><option value="<?php echo $role['id']; ?>" <?php echo ($user['role_id'] == $role['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($role['role_name']); ?></option><?php endforeach; ?></select></div>
        <!-- === بداية الإضافة: قائمة الفروع === -->
        <div class="col-12">
            <label for="branches" class="form-label">الفروع المسموح بها</label>
            <p class="text-muted small">إذا لم تختر أي فرع، سيتمكن المستخدم من رؤية بيانات كل الفروع (إذا كان دوره يسمح بذلك).</p>
            <select class="form-select select2-init" id="branches" name="branches[]" multiple data-placeholder="اختر الفروع المسموح بها...">
                <?php foreach ($branches_list as $branch): ?>
                    <option value="<?php echo $branch['id']; ?>" <?php echo in_array($branch['id'], $current_branch_ids) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($branch['branch_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- === نهاية الإضافة === -->
        <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?php echo ($user['is_active']) ? 'checked' : ''; ?>><label class="form-check-label" for="is_active">المستخدم نشط</label></div></div>
    </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>