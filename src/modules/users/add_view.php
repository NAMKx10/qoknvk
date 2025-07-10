<?php
// src/modules/users/add_view.php (النسخة المصححة)

global $pdo; // ✨ هذا هو السطر الحاسم ✨

// جلب قائمة الأدوار والفروع
$roles_list = $pdo->query("SELECT id, role_name FROM roles WHERE deleted_at IS NULL ORDER BY role_name ASC")->fetchAll();
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE status = 'Active' AND deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();
?>
<div class="modal-header"><h5 class="modal-title">إضافة مستخدم جديد</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="index.php?page=users/handle_add" class="ajax-form">
    <div class="modal-body">
        <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
        <div class="row g-3">
            <div class="col-sm-6"><label class="form-label required">الاسم الكامل</label><input type="text" class="form-control" name="full_name" required></div>
            <div class="col-sm-6"><label class="form-label required">اسم المستخدم</label><input type="text" class="form-control" name="username" required></div>
            <div class="col-sm-6"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email"></div>
            <div class="col-sm-6"><label class="form-label">الجوال</label><input type="text" class="form-control" name="mobile"></div>
            <div class="col-sm-6"><label class="form-label required">كلمة المرور</label><input type="password" class="form-control" name="password" required></div>
            <div class="col-sm-6"><label class="form-label required">الدور</label><select class="form-select" name="role_id" required><?php foreach($roles_list as $role): ?><option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-sm-6">
                <label class="form-label">تاريخ الإنشاء</label>
                <input type="date" class="form-control" name="created_at" value="<?= date('Y-m-d') ?>">
            </div>
            <?php
            $statuses = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'status' AND deleted_at IS NULL ORDER BY display_order")->fetchAll(PDO::FETCH_KEY_PAIR);
            ?>
            <div class="col-sm-6">
                <label class="form-label required">الحالة</label>
                <select class="form-select" name="status" required>
                    <?php foreach ($statuses as $key => $value): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= ($key === 'Active') ? 'selected' : '' ?>><?= htmlspecialchars($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">الفروع المسموح بها</label>
                <p class="form-hint">إذا لم تختر أي فرع، سيتمكن المستخدم من رؤية بيانات كل الفروع.</p>
                <select class="form-select select2-init" name="branches[]" multiple data-placeholder="اختر الفروع...">
                    <?php foreach ($branches_list as $branch): ?>
                        <option value="<?= $branch['id'] ?>"><?= htmlspecialchars($branch['branch_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">حفظ المستخدم</button></div>
</form>