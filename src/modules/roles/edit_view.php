<?php
// src/modules/roles/edit_view.php (الإصدار المطور)

if (!isset($_GET['id'])) { header('Location: index.php?page=roles'); exit(); }
$role_id = $_GET['id'];

$role_stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$role_stmt->execute([$role_id]);
$role = $role_stmt->fetch();
if (!$role) { die("Role not found."); }

$all_permissions_stmt = $pdo->query("
    SELECT p.id, p.description, pg.group_name
    FROM permissions p
    JOIN permission_groups pg ON p.group_id = pg.id
    WHERE p.deleted_at IS NULL AND pg.deleted_at IS NULL
    ORDER BY pg.display_order, p.id
");
$all_permissions = [];
foreach($all_permissions_stmt->fetchAll() as $perm) {
    $all_permissions[$perm['group_name']][] = $perm;
}

$current_permissions_stmt = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
$current_permissions_stmt->execute([$role_id]);
$current_permissions = $current_permissions_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">تعديل صلاحيات الدور: <span class="text-primary"><?= htmlspecialchars($role['role_name']) ?></span></h2>
                <div class="text-muted mt-1"><?= htmlspecialchars($role['description']) ?></div>
            </div>
            <div class="col-auto ms-auto d-print-none">
    <div class="btn-list">
        <!-- (جديد) زر تعديل بيانات الدور -->
        <a href="#" class="btn btn-outline-secondary"
           data-bs-toggle="modal" 
           data-bs-target="#main-modal" 
           data-bs-url="index.php?page=roles/edit_role&id=<?= $role['id'] ?>&view_only=true">
            تعديل بيانات الدور
        </a>
        <a href="index.php?page=roles" class="btn"><i class="ti ti-arrow-left me-2"></i>العودة لقائمة الأدوار</a>
    </div>
</div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <form method="POST" action="index.php?page=roles/handle_edit_permissions">
            <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
            <div class="row row-cards">
                <?php foreach($all_permissions as $group => $permissions): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title"><?= htmlspecialchars($group) ?></h3></div>
                            <div class="card-body">
                                <?php foreach($permissions as $permission): ?>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $permission['id'] ?>" id="perm-<?= $permission['id'] ?>"
                                            <?= in_array($permission['id'], $current_permissions) ? 'checked' : '' ?>
                                            <?= ($role['id'] == 1) ? 'disabled' : '' ?> >
                                        <label class="form-check-label" for="perm-<?= $permission['id'] ?>"><?= htmlspecialchars($permission['description']) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4">
                <?php if($role['id'] != 1): ?>
                    <button type="submit" class="btn btn-primary">حفظ الصلاحيات</button>
                <?php else: ?>
                    <div class="alert alert-warning">لا يمكن تعديل صلاحيات دور "المدير الخارق".</div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>