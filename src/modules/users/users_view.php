<?php
// جلب المستخدمين مع أسماء أدوارهم
$stmt = $pdo->query("
    SELECT u.id, u.full_name, u.username, u.email, u.is_active, r.role_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.deleted_at IS NULL
    ORDER BY u.id ASC
");
$users = $stmt->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-users-cog ms-2"></i>إدارة المستخدمين</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=users/add&view_only=true" data-bs-title="إضافة مستخدم جديد">
            <i class="fas fa-plus-circle ms-1"></i>إضافة مستخدم جديد
        </button>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr><th>#</th><th>الاسم الكامل</th><th>اسم المستخدم</th><th>الدور</th><th>الحالة</th><th>الإجراءات</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                <td>
                    <?php if ($user['is_active']): ?>
                        <span class="badge bg-success">نشط</span>
                    <?php else: ?>
                        <span class="badge bg-danger">معطل</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=users/edit&id=<?php echo $user['id']; ?>&view_only=true" data-bs-title="تعديل المستخدم"><i class="fas fa-edit"></i></button>
                    <a href="index.php?page=users/delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('سيتم نقل المستخدم إلى الأرشيف. هل أنت متأكد؟');"><i class="fas fa-trash-alt"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>