<?php
// =================================================================
// 1. جلب البيانات وهيكلتها
// =================================================================

// جلب كل المجموعات أولاً لعرضها في القائمة الجانبية
$groups_stmt = $pdo->query("SELECT id, group_name, group_key FROM permission_groups WHERE deleted_at IS NULL ORDER BY id ASC");
$groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

// تحديد المجموعة النشطة (أول مجموعة بشكل افتراضي)
$active_group_id = $_GET['group_id'] ?? ($groups[0]['id'] ?? 0);

// جلب الصلاحيات الخاصة بالمجموعة النشطة فقط
$permissions = [];
if ($active_group_id) {
    $permissions_stmt = $pdo->prepare("SELECT * FROM permissions WHERE group_id = ? AND deleted_at IS NULL ORDER BY id ASC");
    $permissions_stmt->execute([$active_group_id]);
    $permissions = $permissions_stmt->fetchAll(PDO::FETCH_ASSOC);
}
// العثور على بيانات المجموعة النشطة لعرضها في العنوان
$active_group = array_values(array_filter($groups, fn($g) => $g['id'] == $active_group_id))[0] ?? null;

?>

<!-- CSS مدمج لتنسيق الواجهة الجديدة -->
<style>
    .permissions-layout {
        display: flex;
        gap: 1.5rem;
    }
    .permissions-nav {
        flex: 0 0 250px; /* عرض ثابت للقائمة الجانبية */
    }
    .permissions-content {
        flex-grow: 1;
    }
    .nav-pills .nav-link {
        text-align: right;
        font-weight: 500;
        color: #495057;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
        color: white;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.4);
    }
</style>

<!-- بداية عرض الواجهة -->

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-key ms-2"></i>إدارة الصلاحيات</h1>
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=permissions/add_group&view_only=true" data-bs-title="إضافة مجموعة صلاحيات جديدة">
        <i class="fas fa-plus-circle ms-1"></i>إضافة مجموعة جديدة
    </button>
</div>

<div class="permissions-layout">
    <!-- 1. القائمة الجانبية للمجموعات -->
    <div class="permissions-nav">
        <div class="list-group">
            <?php foreach ($groups as $group): ?>
                <a href="index.php?page=permissions&group_id=<?= $group['id'] ?>" 
                   class="list-group-item list-group-item-action <?= ($group['id'] == $active_group_id) ? 'active' : '' ?>">
                    <?= htmlspecialchars($group['group_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 2. محتوى المجموعة النشطة -->
    <div class="permissions-content">
        <?php if ($active_group): ?>
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <h5 class="mb-0 d-inline-block"><?= htmlspecialchars($active_group['group_name']) ?></h5>
                        <code class="text-muted small">(<?= htmlspecialchars($active_group['group_key']) ?>)</code>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=permissions/edit_group&id=<?= $active_group_id ?>&view_only=true" data-bs-title="تعديل المجموعة"><i class="fas fa-pen"></i></button>
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=permissions/add&group_id=<?= $active_group_id ?>&view_only=true" data-bs-title="إضافة صلاحية جديدة"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>الوصف</th>
                                    <th>المفتاح البرمجي (Key)</th>
                                    <th class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($permissions)): ?>
                                    <tr><td colspan="3" class="text-center text-muted p-4">لا توجد صلاحيات في هذه المجموعة.</td></tr>
                                <?php else: foreach ($permissions as $permission): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($permission['description']) ?></td>
                                        <td><code><?= htmlspecialchars($permission['permission_key']) ?></code></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=permissions/edit&id=<?= $permission['id']; ?>&view_only=true" data-bs-title="تعديل الصلاحية"><i class="fas fa-edit"></i></button>
                                            <a href="index.php?page=permissions/delete&id=<?= $permission['id']; ?>" class="btn btn-sm btn-danger" title="أرشفة" onclick="return confirm('سيتم نقل هذه الصلاحية إلى الأرشيف. هل أنت متأكد؟');"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">يرجى إضافة مجموعة صلاحيات أولاً للبدء.</div>
        <?php endif; ?>
    </div>
</div>