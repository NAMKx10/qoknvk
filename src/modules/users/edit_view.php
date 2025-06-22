<?php
// src/modules/users/edit_view.php (الإصدار المصحح والنهائي)

if (!isset($_GET['id'])) { die("ID is required."); }
$user_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) { die("User not found."); }

// جلب قائمة الأدوار
$roles_list = $pdo->query("SELECT id, role_name FROM roles WHERE deleted_at IS NULL ORDER BY role_name ASC")->fetchAll();
// جلب قائمة كل الفروع النشطة
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'نشط' AND deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();
// جلب الفروع المرتبطة حاليًا بهذا المستخدم
$current_branches_stmt = $pdo->prepare("SELECT branch_id FROM user_branches WHERE user_id = ?");
$current_branches_stmt->execute([$user_id]);
$current_branch_ids = $current_branches_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="modal-header">
    <h5 class="modal-title">تعديل المستخدم: <?= htmlspecialchars($user['full_name']) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form method="POST" action="index.php?page=users/handle_edit" class="ajax-form">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-sm-6"><label class="form-label required">الاسم الكامل</label><input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required></div>
            <div class="col-sm-6"><label class="form-label required">اسم المستخدم</label><input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required></div>
            <div class="col-sm-6"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>"></div>
            <div class="col-sm-6"><label class="form-label">الجوال</label><input type="text" class="form-control" name="mobile" value="<?= htmlspecialchars($user['mobile']) ?>"></div>
            <div class="col-sm-6"><label class="form-label">كلمة المرور الجديدة</label><input type="password" class="form-control" name="password" placeholder="اتركه فارغاً لعدم التغيير"></div>
            <div class="col-sm-6"><label class="form-label required">الدور</label><select class="form-select" name="role_id" required><?php foreach($roles_list as $role): ?><option value="<?= $role['id'] ?>" <?= ($user['role_id'] == $role['id']) ? 'selected' : '' ?>><?= htmlspecialchars($role['role_name']) ?></option><?php endforeach; ?></select></div>
                        <!-- (جديد) حقل تاريخ الإنشاء -->
            <div class="col-sm-6">
                <label class="form-label">تاريخ الإنشاء</label>
                <input type="date" class="form-control" name="created_at" value="<?= date('Y-m-d', strtotime($user['created_at'])) ?>">
            </div>

            <!-- (مُحسَّن) حقل الحالة -->
            <div class="col-sm-6 d-flex align-items-center">
                 <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active" <?= ($user['is_active']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="edit_is_active">المستخدم نشط</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">الفروع المسموح بها</label>
                <p class="form-hint">إذا لم تختر أي فرع، سيتمكن المستخدم من رؤية بيانات كل الفروع.</p>
                <select class="form-select select2-init" name="branches[]" multiple data-placeholder="اختر الفروع...">
                    <?php foreach ($branches_list as $branch): ?>
                        <option value="<?= $branch['id'] ?>" <?= in_array($branch['id'], $current_branch_ids) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($branch['branch_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
    </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
    </div>
</form>