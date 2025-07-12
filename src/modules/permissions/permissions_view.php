<!-- src/modules/permissions/permissions_view.php (النسخة النظيفة والمؤمنة) -->

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">إدارة الصلاحيات والمجموعات</h2></div>
            <div class="col-auto ms-auto d-print-none">
                <?php if (has_permission('add_permission_group')): ?>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=permissions/add_group&view_only=true">
                    <i class="ti ti-plus me-2"></i>إضافة مجموعة جديدة
                </a>
                <?php endif; ?>
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
                            <?php if (has_permission('add_permission')): ?>
                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=permissions/add&group_id=<?= $active_group_id ?>&view_only=true"><i class="ti ti-plus me-1"></i> إضافة صلاحية</a>
                            <?php endif; ?>
                            <?php if (has_permission('edit_permission_group')): ?>
                             <a href="#" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=permissions/edit_group&id=<?= $active_group_id ?>&view_only=true">تعديل المجموعة</a>
                            <?php endif; ?>
                            <?php if (has_permission('delete_permission_group')): ?>
                             <a href="index.php?page=permissions/delete_group&id=<?= $active_group['id'] ?>" class="btn btn-outline-danger confirm-delete">حذف المجموعة</a>
                            <?php endif; ?>
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
                                                <?php if (has_permission('edit_permission')): ?>
                                                <a href="#" class="btn btn-icon" title="تعديل الصلاحية" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=permissions/edit&id=<?= $permission['id'] ?>&view_only=true"><i class="ti ti-edit"></i></a>
                                                <?php endif; ?>
                                                <?php if (has_permission('delete_permission')): ?>
                                                <a href="index.php?page=permissions/delete&id=<?= $permission['id'] ?>" class="btn btn-icon text-danger confirm-delete" title="حذف الصلاحية"><i class="ti ti-trash"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">يرجى تحديد أو إضافة مجموعة لعرض صلاحياتها.</div>
            <?php endif; ?>
        </div>
    </div>
</div></div>