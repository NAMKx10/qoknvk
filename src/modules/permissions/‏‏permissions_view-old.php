<?php
// ... (نفس كود جلب البيانات في الأعلى) ...
$stmt = $pdo->query("
    SELECT 
        pg.id as group_id,
        pg.group_name,
        pg.description as group_description,
        p.id as permission_id,
        p.description,
        p.permission_key
    FROM permission_groups pg
    LEFT JOIN permissions p ON pg.id = p.group_id AND p.deleted_at IS NULL
    WHERE pg.deleted_at IS NULL
    ORDER BY pg.group_name, p.id
");

$grouped_permissions = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $grouped_permissions[$row['group_id']]['group_name'] = $row['group_name'];
    $grouped_permissions[$row['group_id']]['group_description'] = $row['group_description'];
    if ($row['permission_id']) {
        $grouped_permissions[$row['group_id']]['permissions'][] = $row;
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-key ms-2"></i>إدارة الصلاحيات</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=permissions/add_group&view_only=true" data-bs-title="إضافة مجموعة صلاحيات جديدة">
            <i class="fas fa-plus-circle ms-1"></i>إضافة مجموعة جديدة
        </button>
    </div>
</div>

<div class="alert alert-info"><i class="fas fa-info-circle ms-2"></i>من هنا يمكنك التحكم في كل الصلاحيات ومجموعاتها في النظام.</div>

<?php if (empty($grouped_permissions)): ?>
    <div class="alert alert-success text-center">لا توجد مجموعات صلاحيات معرفة في النظام بعد.</div>
<?php else: ?>
    <?php foreach ($grouped_permissions as $group_id => $group_data): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 d-inline-block">
                        <?php echo htmlspecialchars($group_data['group_name']); ?>
                    </h5>
                    <a href="index.php?page=permissions/delete_group&id=<?php echo $group_id; ?>" class="btn btn-sm btn-outline-danger p-1 lh-1" title="حذف المجموعة" onclick="return confirm('سيتم نقل المجموعة وكل صلاحياتها إلى الأرشيف. هل أنت متأكد؟');"><i class="fas fa-trash-alt fa-sm"></i></a>
                    <button type="button" class="btn btn-sm btn-outline-secondary p-1 lh-1" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=permissions/edit_group&id=<?php echo $group_id; ?>&view_only=true" data-bs-title="تعديل المجموعة" title="تعديل المجموعة"><i class="fas fa-pen fa-sm"></i></button>
                    <?php if (!empty($group_data['group_description'])): ?>
                        <small class="d-block text-muted"><?php echo htmlspecialchars($group_data['group_description']); ?></small>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=permissions/add&group_id=<?php echo $group_id; ?>&view_only=true" data-bs-title="إضافة صلاحية جديدة">
                    <i class="fas fa-plus ms-1"></i> إضافة صلاحية
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الوصف</th>
                            <th>المفتاح البرمجي</th>
                            <th class="text-center" style="width: 100px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($group_data['permissions'])): ?>
                            <?php foreach ($group_data['permissions'] as $permission): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($permission['description']); ?></td>
                                    <td><code><?php echo htmlspecialchars($permission['permission_key']); ?></code></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=permissions/edit&id=<?php echo $permission['permission_id']; ?>&view_only=true" data-bs-title="تعديل الصلاحية" title="تعديل"><i class="fas fa-edit"></i></button>
                                        <a href="index.php?page=permissions/delete&id=<?php echo $permission['permission_id']; ?>" class="btn btn-sm btn-danger" title="أرشفة" onclick="return confirm('سيتم نقل هذه الصلاحية إلى الأرشيف. هل أنت متأكد؟');"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                             <tr><td colspan="3" class="text-center text-muted p-3">لا توجد صلاحيات في هذه المجموعة بعد.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>