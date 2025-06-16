<?php
if (!isset($_GET['id'])) { header('Location: index.php?page=roles'); exit(); }
$role_id = $_GET['id'];

// جلب معلومات الدور
$role_stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$role_stmt->execute([$role_id]);
$role = $role_stmt->fetch();
if (!$role) { die("Role not found."); }

// جلب كل الصلاحيات المتاحة وتجميعها حسب المجموعة الجديدة
$all_permissions_stmt = $pdo->query("
    SELECT p.id, p.description, pg.group_name
    FROM permissions p
    JOIN permission_groups pg ON p.group_id = pg.id
    WHERE p.deleted_at IS NULL AND pg.deleted_at IS NULL
    ORDER BY pg.group_name, p.id
");
$all_permissions = [];
foreach($all_permissions_stmt->fetchAll() as $perm) {
    $all_permissions[$perm['group_name']][] = $perm;
}

// جلب الصلاحيات الحالية لهذا الدور
$current_permissions_stmt = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
$current_permissions_stmt->execute([$role_id]);
$current_permissions = $current_permissions_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">تعديل صلاحيات الدور: <?php echo htmlspecialchars($role['role_name']); ?></h1>
    <a href="index.php?page=roles" class="btn btn-sm btn-outline-secondary">العودة لقائمة الأدوار</a>
</div>

<form method="POST" action="index.php?page=roles/handle_edit">
    <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
    <?php foreach($all_permissions as $group => $permissions): ?>
        <fieldset class="mb-4">
            <legend class="h5"><?php echo htmlspecialchars($group); ?></legend>
            <div class="row">
                <?php foreach($permissions as $permission): ?>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $permission['id']; ?>" id="perm-<?php echo $permission['id']; ?>"
                                <?php if(in_array($permission['id'], $current_permissions)) echo 'checked'; ?>
                                <?php if($role['id'] == 1) echo 'disabled'; // Super Admin cannot be edited ?> >
                            <label class="form-check-label" for="perm-<?php echo $permission['id']; ?>">
                                <?php echo htmlspecialchars($permission['description']); ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>
    <?php endforeach; ?>
    <hr>
    <?php if($role['id'] != 1): // Super Admin cannot be edited ?>
    <button type="submit" class="btn btn-primary">حفظ الصلاحيات</button>
    <?php endif; ?>
</form>