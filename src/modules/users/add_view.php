<?php
// جلب قائمة الأدوار
$roles_stmt = $pdo->query("SELECT id, role_name FROM roles ORDER BY role_name ASC");
$roles_list = $roles_stmt->fetchAll();
?>
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<form method="POST" action="index.php?page=users/handle_add_ajax" class="ajax-form">
    <div class="row g-3">
        <div class="col-sm-6"><label for="full_name" class="form-label">الاسم الكامل</label><input type="text" class="form-control" id="full_name" name="full_name" required></div>
        <div class="col-sm-6"><label for="username" class="form-label">اسم المستخدم (للدخول)</label><input type="text" class="form-control" id="username" name="username" required></div>
        <div class="col-sm-6"><label for="email" class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" id="email" name="email"></div>
        <div class="col-sm-6"><label for="mobile" class="form-label">الجوال</label><input type="text" class="form-control" id="mobile" name="mobile"></div>
        <div class="col-sm-6"><label for="password" class="form-label">كلمة المرور</label><input type="password" class="form-control" id="password" name="password" required></div>
        <div class="col-sm-6"><label for="role_id" class="form-label">الدور (الصلاحية)</label><select class="form-select" id="role_id" name="role_id" required><?php foreach($roles_list as $role): ?><option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option><?php endforeach; ?></select></div>
    </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary">حفظ المستخدم</button>
    </div>
</form>