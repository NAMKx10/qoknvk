<?php
// src/modules/users/users_view.php (الإصدار الاحترافي - نسخة طبق الأصل من العقارات)

// --- 1. الإعدادات والفلترة ---
$limit = $_GET['limit'] ?? 10;
$current_page = $_GET['p'] ?? 1;
$offset = ($current_page - 1) * $limit;

$filter_q = $_GET['q'] ?? null;
$filter_role_id = $_GET['role_id'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;

// --- 2. بناء الاستعلام ---
$joins = "
    LEFT JOIN roles r ON u.role_id = r.id
    LEFT JOIN user_branches ub ON u.id = ub.user_id
    LEFT JOIN branches b ON ub.branch_id = b.id
";
$where_conditions = ["u.deleted_at IS NULL"];
$params = [];

if (!empty($filter_q)) {
    $where_conditions[] = "(u.full_name LIKE :q OR u.username LIKE :q OR u.email LIKE :q)";
    $params[':q'] = '%' . $filter_q . '%';
}
if (!empty($filter_role_id)) {
    $where_conditions[] = "u.role_id = :role_id";
    $params[':role_id'] = $filter_role_id;
}
if (!empty($filter_branch_id)) {
    // هذا الشرط يبحث عن المستخدمين المرتبطين بالفرع المحدد
    $where_conditions[] = "u.id IN (SELECT user_id FROM user_branches WHERE branch_id = :branch_id)";
    $params[':branch_id'] = $filter_branch_id;
}

$sql_where = " WHERE " . implode(" AND ", $where_conditions);

// --- 3. جلب الإحصائيات ---
$stats_sql = "SELECT (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) as total, (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL AND is_active = 1) as active";
$stats = $pdo->query($stats_sql)->fetch(PDO::FETCH_ASSOC);
$stats['inactive'] = $stats['total'] - $stats['active'];

// --- 4. جلب البيانات الرئيسية ---
$count_query = "SELECT COUNT(DISTINCT u.id) FROM users u {$joins} {$sql_where}";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

if ($total_records > 0) {
    $data_query = "
        SELECT DISTINCT u.id, u.full_name, u.username, u.email, u.is_active, r.role_name, 
               (SELECT GROUP_CONCAT(b_inner.branch_code SEPARATOR ', ') 
                FROM user_branches ub_inner 
                JOIN branches b_inner ON ub_inner.branch_id = b_inner.id 
                WHERE ub_inner.user_id = u.id) as branch_codes
        FROM users u {$joins} {$sql_where}
        GROUP BY u.id
        ORDER BY u.id ASC 
        LIMIT {$limit} OFFSET {$offset}
    ";
    $data_stmt = $pdo->prepare($data_query);
    $data_stmt->execute($params);
    $users = $data_stmt->fetchAll();
} else {
    $users = [];
}

// --- 5. جلب بيانات الفلاتر ---
$roles_list = $pdo->query("SELECT id, role_name FROM roles WHERE deleted_at IS NULL ORDER BY role_name")->fetchAll();
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL ORDER BY branch_name")->fetchAll();
?>

<!-- =============================================== -->
<!-- HTML: واجهة المستخدمين المطورة                   -->
<!-- =============================================== -->

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">إدارة المستخدمين (<?= $total_records ?>)</h2></div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <button onclick="window.print();" class="btn btn-outline-secondary"><i class="ti ti-printer me-2"></i>طباعة</button>
                    <a href="#" class="btn"><i class="ti ti-upload me-2"></i>إجراءات متعددة</a>
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=users/add&view_only=true"><i class="ti ti-plus me-2"></i>إضافة مستخدم</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- بطاقات الإحصائيات -->
        <div class="row row-cards mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-primary-fg">
                    <div class="card-stamp"><div class="card-stamp-icon bg-white text-primary"><i class="ti ti-users"></i></div></div>
                    <div class="card-body">
                        <h3 class="card-title m-0">إجمالي المستخدمين</h3>
                        <p class="h1 mt-1 mb-0"><?= $stats['total'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-success-fg">
                    <div class="card-stamp"><div class="card-stamp-icon bg-white text-success"><i class="ti ti-user-check"></i></div></div>
                    <div class="card-body">
                        <h3 class="card-title m-0">المستخدمين النشطين</h3>
                        <p class="h1 mt-1 mb-0"><?= $stats['active'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-danger-fg">
                    <div class="card-stamp"><div class="card-stamp-icon bg-white text-danger"><i class="ti ti-user-off"></i></div></div>
                    <div class="card-body">
                        <h3 class="card-title m-0">المستخدمين المعطلين</h3>
                        <p class="h1 mt-1 mb-0"><?= $stats['inactive'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- بطاقة الفلترة المنفصلة -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="index.php" method="GET">
                    <input type="hidden" name="page" value="users">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">بحث شامل</label><input type="search" name="q" class="form-control" placeholder="ابحث بالاسم، المستخدم، الإيميل..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">الدور</label><select name="role_id" class="form-select"><option value="">كل الأدوار</option><?php foreach($roles_list as $item):?><option value="<?=$item['id']?>" <?= ($filter_role_id == $item['id'])?'selected':''?>><?=htmlspecialchars($item['role_name'])?></option><?php endforeach;?></select></div>
                        <div class="col-md-3"><label class="form-label">الفرع</label><select name="branch_id" class="form-select"><option value="">كل الفروع</option><?php foreach($branches_list as $item):?><option value="<?=$item['id']?>" <?= ($filter_branch_id == $item['id'])?'selected':''?>><?=htmlspecialchars($item['branch_name'])?></option><?php endforeach;?></select></div>
                        <div class="col-md-2 d-flex align-items-end"><div class="btn-list"><button type="submit" class="btn btn-primary">تطبيق</button><a href="index.php?page=users" class="btn"><i class="ti ti-refresh"></i></a></div></div>
                    </div>
                </form>
            </div>
        </div>

        <!-- بطاقة الجدول الرئيسية -->
        <div class="card">
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap table-hover">
                    <thead><tr><th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" onchange="toggleAllCheckboxes(this)"></th><th>م</th><th>الاسم الكامل</th><th>الدور</th><th>الفروع</th><th>الحالة</th><th class="w-1"></th></tr></thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="7" class="text-center p-4">لا توجد نتائج تطابق بحثك.</td></tr>
                        <?php else: $row_counter = $offset + 1; foreach($users as $user): ?>
                        <tr>
                            <td><input class="form-check-input m-0 align-middle" type="checkbox" name="row_id[]" value="<?= $user['id'] ?>"></td>
                            <td><span class="text-muted"><?= $row_counter++ ?></span></td>
                            <td>
                                <div><?= htmlspecialchars($user['full_name']) ?></div>
                                <div class="text-muted"><?= htmlspecialchars($user['username']) ?></div>
                            </td>
                            <td><span class="badge bg-secondary-lt"><?= htmlspecialchars($user['role_name']) ?></span></td>
                            <td>
                                <?php if (empty($user['branch_codes'])): ?>
                                    <span class="badge bg-green-lt">كل الفروع</span>
                                <?php else: ?>
                                    <span class="text-muted" data-bs-toggle="tooltip" title="<?= htmlspecialchars($user['branch_codes']) ?>"><?= htmlspecialchars(substr($user['branch_codes'], 0, 30)) ?>...</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?> me-1"></span> <?= $user['is_active'] ? 'نشط' : 'معطل' ?>
                            </td>
                            <td class="text-end"><div class="btn-list flex-nowrap"><a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=users/edit&id=<?= $user['id']; ?>&view_only=true">تعديل</a></div></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center">
                <p class="m-0 text-muted">عرض <span><?= count($users) ?></span> من <span><?= $total_records ?></span> سجل</p>
                <div class="m-auto"><?php render_smart_pagination($current_page, $total_pages, $_GET); ?></div>
            </div>
        </div>
    </div>
</div>