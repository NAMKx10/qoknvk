<?php
// src/modules/permissions/permissions_view.php (الإصدار المطور)

// جلب المجموعات مع عدد الصلاحيات في كل منها
$groups_stmt = $pdo->query("
    SELECT pg.*, COUNT(p.id) as permissions_count
    FROM permission_groups pg
    LEFT JOIN permissions p ON pg.id = p.group_id AND p.deleted_at IS NULL
    WHERE pg.deleted_at IS NULL
    GROUP BY pg.id
    ORDER BY pg.id ASC
");
$groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

$active_group_id = $_GET['group_id'] ?? ($groups[0]['id'] ?? 0);

$permissions = [];
if ($active_group_id) {
    $permissions_stmt = $pdo->prepare("SELECT * FROM permissions WHERE group_id = ? AND deleted_at IS NULL ORDER BY id ASC");
    $permissions_stmt->execute([$active_group_id]);
    $permissions = $permissions_stmt->fetchAll(PDO::FETCH_ASSOC);
}
$active_group = array_values(array_filter($groups, fn($g) => $g['id'] == $active_group_id))[0] ?? null;
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">إدارة الصلاحيات والمجموعات</h2></div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=permissions/add_group&view_only=true">
                    <i class="ti ti-plus me-2"></i>إضافة مجموعة جديدة
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body"><div class="container-xl">
    <div class="row gx-lg-4">
        <div class="col-lg-4">
            <div class="list-group mb-3">
                <?php foreach ($groups as $group): ?>
                    <a href="index.php?page=permissions&group_id=<?= $group['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= ($group['id'] == $active_group_id) ? 'active' : '' ?>">
                        <?= htmlspecialchars($group['group_name']) ?>
                        <span class="badge bg-primary-lt"><?= $group['permissions_count'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-8">
            <?php if ($active_group): ?>
                <div class="card">
                    <div class="card-header">
                        <div>
    <h3 class="card-title mb-0"><?= htmlspecialchars($active_group['group_name']) ?></h3>
    <code class="text-muted d-block mt-1"><?= htmlspecialchars($active_group['group_key']) ?></code>
</div>
                        <div class="card-actions">
                             <a href="#" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=permissions/edit_group&id=<?= $active_group_id ?>&view_only=true">تعديل المجموعة</a>
                             <a href="index.php?page=permissions/delete_group&id=<?= $active_group['id'] ?>" class="btn btn-outline-danger confirm-delete">حذف المجموعة</a>
                             <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=permissions/add&group_id=<?= $active_group_id ?>&view_only=true"><i class="ti ti-plus me-1"></i> إضافة صلاحية</a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead><tr><th>الوصف</th><th>المفتاح البرمجي</th><th class="w-1"></th></tr></thead>
                            <tbody>
                                <?php if(empty($permissions)): ?>
                                    <tr><td colspan="3" class="text-center text-muted p-4">لا توجد صلاحيات في هذه المجموعة.</td></tr>
                                <?php else: foreach ($permissions as $permission): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($permission['description']) ?></td>
                                        <td><code><?= htmlspecialchars($permission['permission_key']) ?></code></td>
                                        <td class="text-end">
    <div class="btn-list flex-nowrap">
        <!-- زر التعديل (أيقونة) -->
        <a href="#" class="btn btn-icon" title="تعديل الصلاحية"
           data-bs-toggle="modal" 
           data-bs-target="#main-modal" 
           data-bs-url="index.php?page=permissions/edit&id=<?= $permission['id'] ?>&view_only=true">
            <i class="ti ti-edit"></i>
        </a>
        <!-- زر الحذف (أيقونة) -->
        <a href="index.php?page=permissions/delete&id=<?= $permission['id'] ?>" 
           class="btn btn-icon text-danger confirm-delete" 
           title="حذف الصلاحية">
            <i class="ti ti-trash"></i>
        </a>
    </div>
</td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">يرجى إضافة مجموعة صلاحيات أولاً للبدء.</div>
            <?php endif; ?>
        </div>
    </div>
</div></div>