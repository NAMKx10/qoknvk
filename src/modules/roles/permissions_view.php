<?php
// src/modules/permissions/permissions_view.php (الإصدار المطور)

$all_permissions_stmt = $pdo->query("
    SELECT p.*, pg.group_name 
    FROM permissions p
    LEFT JOIN permission_groups pg ON p.group_id = pg.id
    WHERE p.deleted_at IS NULL
    ORDER BY pg.display_order, p.id
");
$permissions = $all_permissions_stmt->fetchAll();

$groups = $pdo->query("SELECT id, group_name FROM permission_groups WHERE deleted_at IS NULL ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <h2 class="page-title">إدارة الصلاحيات</h2>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row gx-lg-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap">
                            <thead><tr><th>الوصف</th><th>المفتاح البرمجي</th><th>المجموعة</th></tr></thead>
                            <tbody>
                                <?php foreach($permissions as $perm): ?>
                                <tr>
                                    <td><?= htmlspecialchars($perm['description']) ?></td>
                                    <td><code><?= htmlspecialchars($perm['permission_key']) ?></code></td>
                                    <td><span class="badge bg-secondary-lt"><?= htmlspecialchars($perm['group_name']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">إضافة صلاحية جديدة</h3></div>
                    <div class="card-body">
                        <form method="POST" action="index.php?page=permissions/handle_add" class="ajax-form">
                            <div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
                            <div class="mb-3"><label class="form-label required">الوصف</label><input type="text" class="form-control" name="description" required></div>
                            <div class="mb-3"><label class="form-label required">المفتاح (انجليزي)</label><input type="text" class="form-control" name="permission_key" required></div>
                            <div class="mb-3">
                                <label class="form-label required">المجموعة</label>
                                <select name="group_id" class="form-select" required>
                                    <option value="">-- اختر مجموعة --</option>
                                    <?php foreach($groups as $group): ?>
                                        <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['group_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mt-4"><button type="submit" class="btn btn-primary w-100">إضافة الصلاحية</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>