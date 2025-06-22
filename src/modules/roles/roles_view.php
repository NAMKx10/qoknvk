<?php
// src/modules/roles/roles_view.php (الإصدار المصحح مع زر الحذف)
$stmt  = $pdo->query("
    SELECT r.*, COUNT(u.id) as users_count
    FROM roles r
    LEFT JOIN users u ON r.id = u.role_id AND u.deleted_at IS NULL
    WHERE r.deleted_at IS NULL 
    GROUP BY r.id
    ORDER BY r.id ASC
");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">إدارة الأدوار والصلاحيات</h2></div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=roles/add&view_only=true">
                    <i class="ti ti-plus me-2"></i>إضافة دور جديد
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap">
                    <thead><tr><th>اسم الدور</th><th>الوصف</th><th>عدد المستخدمين</th><th class="w-1"></th></tr></thead>
                    <tbody>
                        <?php foreach ($roles as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['role_name']); ?></td>
                            <td><span class="text-muted"><?= htmlspecialchars($row['description']); ?></span></td>
                            <td><span class="badge bg-blue-lt"><?= $row['users_count']; ?></span></td>
                            <td class="text-end">
                                <div class="btn-list flex-nowrap">
                                    <a href="index.php?page=roles/edit&id=<?= $row['id']; ?>" class="btn">
                                        <i class="ti ti-key me-2"></i>تعديل الصلاحيات
                                    </a>
                                    <?php if ($row['id'] > 2): // لا تسمح بحذف أول دورين ?>
                                        <a href="index.php?page=roles/delete&id=<?= $row['id']; ?>" class="btn btn-outline-danger btn-icon confirm-delete" title="حذف الدور">
                                            <i class="ti ti-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>