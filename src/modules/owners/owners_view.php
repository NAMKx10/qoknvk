<?php
// src/modules/owners/owners_view.php (النسخة النهائية الصحيحة)

// 1. الإعدادات والفلترة
$records_per_page_options = [10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : 10;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

$filter_q = $_GET['q'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_status = $_GET['status'] ?? null;

// --- بناء الاستعلام ---
$sql_where = " WHERE o.deleted_at IS NULL ";
$params = [];
if ($_SESSION['user_branch_ids'] !== 'ALL' && !empty($_SESSION['user_branch_ids'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['user_branch_ids']), '?'));
    $sql_where .= " AND EXISTS (SELECT 1 FROM owner_branches ob WHERE ob.owner_id = o.id AND ob.branch_id IN ($placeholders)) ";
    foreach ($_SESSION['user_branch_ids'] as $branch_id) { $params[] = $branch_id; }
}
if (!empty($filter_q)) { $search_term = '%' . $filter_q . '%'; $sql_where .= " AND (o.owner_name LIKE ? OR o.owner_code LIKE ?)"; array_push($params, $search_term, $search_term); }
if (!empty($filter_branch_id)) { $sql_where .= " AND o.id IN (SELECT owner_id FROM owner_branches WHERE branch_id = ?) "; $params[] = $filter_branch_id; }
if (!empty($filter_type)) { $sql_where .= " AND o.owner_type = ? "; $params[] = $filter_type; }
if (!empty($filter_status)) { $sql_where .= " AND o.status = ? "; $params[] = $filter_status; }

// --- الإحصائيات (باستخدام القيمة 'نشط' الصحيحة) ---
$stats_query = "SELECT COUNT(*) as total, 
    SUM(CASE WHEN status='Active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN owner_type='منشأة' THEN 1 ELSE 0 END) as companies,
    SUM(CASE WHEN owner_type='فرد' THEN 1 ELSE 0 END) as individuals
FROM owners WHERE deleted_at IS NULL";
$stats = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC);

// --- جلب البيانات ---
$total_records_stmt = $pdo->prepare("SELECT COUNT(DISTINCT o.id) FROM owners o {$sql_where}");
$total_records_stmt->execute($params);
$total_records = $total_records_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

$data_sql = "SELECT o.*, GROUP_CONCAT(b.branch_name SEPARATOR ', ') as branch_names
    FROM owners o LEFT JOIN owner_branches ob ON o.id = ob.owner_id LEFT JOIN branches b ON ob.branch_id = b.id
    {$sql_where} GROUP BY o.id ORDER BY o.id DESC LIMIT {$limit} OFFSET {$offset}";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$owners = $data_stmt->fetchAll();

// --- بيانات الفلاتر (باستخدام الطريقة الموحدة والبسيطة) ---
$branches_for_filter = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL")->fetchAll();
$owner_types_filter = $pdo->query("SELECT option_value FROM lookup_options WHERE group_key = 'entity_type' AND option_key != 'entity_type' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_COLUMN);
$statuses_map_stmt = $pdo->query("SELECT option_key, option_value, bg_color FROM lookup_options WHERE group_key = 'status' AND option_key != 'status'");
$statuses_map = [];
foreach ($statuses_map_stmt as $row) {
    // بناء مصفوفة بسيطة تعمل مع كل شيء (الجدول والفلتر)
    $statuses_map[$row['option_key']] = ['name' => $row['option_value'], 'bg_color' => $row['bg_color']];
}

?>

<!-- === HTML (النسخة النهائية الكاملة والصحيحة) === -->
<div class="card">
    <div class="card-body">
        <!-- 1. صف العنوان والأزرار العلوية -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h2 class="page-title mb-0">إدارة الملاك</h2></div>
            <div class="btn-list">
                <button onclick="window.print();" class="btn btn-outline-secondary"><i class="ti ti-printer me-2"></i>طباعة</button>
                <a href="#" class="btn"><i class="ti ti-table-plus me-2"></i>إدخال متعدد</a>
                <a href="#" class="btn"><i class="ti ti-upload me-2"></i>إجراءات متعددة</a>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=owners/add&view_only=true"><i class="ti ti-plus me-2"></i>إضافة مالك</a>
            </div>
        </div>

        <!-- 2. بطاقات الإحصائيات (4 بطاقات) -->
        <div class="row row-cards mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-primary-fg">
                    <div class="card-stamp"><div class="card-stamp-icon bg-white text-primary"><i class="ti ti-users"></i></div></div>
                    <div class="card-body"><h3 class="card-title m-0">إجمالي الملاك</h3><p class="h1 mt-1 mb-0"><?= $stats['total'] ?? 0 ?></p></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-success-fg">
                    <div class="card-stamp"><div class="card-stamp-icon bg-white text-success"><i class="ti ti-user-check"></i></div></div>
                    <div class="card-body"><h3 class="card-title m-0">الملاك النشطون</h3><p class="h1 mt-1 mb-0"><?= $stats['active'] ?? 0 ?></p></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-info-fg">
                    <div class="card-stamp"><div class="card-stamp-icon bg-white text-info"><i class="ti ti-building-skyscraper"></i></div></div>
                    <div class="card-body"><h3 class="card-title m-0">ملاك (منشأة)</h3><p class="h1 mt-1 mb-0"><?= $stats['companies'] ?? 0 ?></p></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-warning-fg">
                    <div class="card-stamp"><div class="card-stamp-icon bg-white text-warning"><i class="ti ti-user-circle"></i></div></div>
                    <div class="card-body"><h3 class="card-title m-0">ملاك (فرد)</h3><p class="h1 mt-1 mb-0"><?= $stats['individuals'] ?? 0 ?></p></div>
                </div>
            </div>
        </div>
        
        <!-- 3. قسم الفلترة (مع الإصلاح) -->
        <form action="index.php" method="GET"><input type="hidden" name="page" value="owners">
            <div class="row g-3">
                <div class="col-md-3"><input type="search" name="q" class="form-control" placeholder="بحث بالاسم أو الكود..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
                <div class="col-md-3"><select class="form-select select2-init" name="branch_id"><option value="">كل الفروع</option><?php foreach($branches_for_filter as $branch):?><option value="<?=$branch['id']?>" <?= ($filter_branch_id == $branch['id'])?'selected':''?>><?=htmlspecialchars($branch['branch_name'])?></option><?php endforeach;?></select></div>
                <div class="col-md-2"><select class="form-select" name="type"><option value="">كل الأنواع</option><?php foreach($owner_types_filter as $type):?><option value="<?=$type?>" <?= ($filter_type == $type)?'selected':''?>><?=htmlspecialchars($type)?></option><?php endforeach;?></select></div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">كل الحالات</option>
                        <?php foreach($statuses_map as $key => $val):?>
                            <option value="<?=htmlspecialchars($key)?>" <?=($filter_status==$key)?'selected':''?>>
                                <?=htmlspecialchars($val['name'])?>
                            </option>
                        <?php endforeach;?>
                    </select>
                </div>
                <div class="col-md-2 d-flex"><button type="submit" class="btn btn-primary w-100">بحث</button><a href="index.php?page=owners" class="btn btn-ghost-secondary ms-2"><i class="ti ti-refresh"></i></a></div>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="table-responsive">
    <table class="table card-table table-vcenter text-nowrap table-hover table-selectable">
        <thead>
            <tr>
                <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" onchange="toggleAllCheckboxes(this)"></th>
                <th>م</th>
                <th>صورة</th>
                <th>المالك</th>
                <th>نوع المالك</th>
                <th>البيانات الرئيسية</th>
                <th>الفروع المرتبطة</th>
                <th>الحالة</th>
                <th>ملاحظات</th>
                <th class="w-1"></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($owners)): ?>
            <tr><td colspan="10" class="text-center p-4">لا توجد نتائج تطابق بحثك.</td></tr>
        <?php else:
            $row_counter = $offset + 1; 
            foreach($owners as $owner): ?>
            <tr>
                <td><input class="form-check-input m-0 align-middle" type="checkbox" name="row_id[]" value="<?= $owner['id'] ?>"></td>
                <td><span class="text-muted"><?= $row_counter++ ?></span></td>
                <td><span class="avatar" style="background-image: url(./assets/static/avatars/default-user.svg)"></span></td>
                <td>
                    <div class="fw-bold"><?= htmlspecialchars($owner['owner_name']) ?></div>
                    <div class="text-muted">كود: <?= htmlspecialchars($owner['owner_code'] ?? 'N/A') ?></div>
                </td>
                <td><?= htmlspecialchars($owner['owner_type']) ?></td>
                <td>
                    <div class="fw-bold">السجل: <?= htmlspecialchars($owner['id_number'] ?? '—') ?></div>
                    <div class="text-muted">جوال: <?= htmlspecialchars($owner['mobile'] ?? '—') ?></div>
                </td>
                <td>
                    <span class="text-muted" data-bs-toggle="tooltip" title="<?= htmlspecialchars($owner['branch_names'] ?? 'غير مرتبط') ?>">
                        <?= htmlspecialchars(substr($owner['branch_names'] ?? '—', 0, 30)) ?><?php if(strlen($owner['branch_names'] ?? '') > 30) echo '...'; ?>
                    </span>
                </td>
                <td>
                    <?php 
                        $status_key = $owner['status'];
                        $status_info = $statuses_map[$status_key] ?? null;
                    ?>
                    <span class="badge" style="background-color: <?= htmlspecialchars($status_info['bg_color'] ?? '#6c757d') ?>; color: #fff;">
                        <?= htmlspecialchars($status_info['name'] ?? $status_key) ?>
                    </span>
                </td>
                <td>
                    <?php if (!empty($owner['notes'])): ?>
                        <i class="ti ti-info-circle text-primary" data-bs-toggle="tooltip" title="<?= htmlspecialchars($owner['notes']) ?>"></i>
                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-1">
                        <a href="#" class="btn btn-icon btn-outline-secondary" title="طباعة ملف المالك"><i class="ti ti-printer"></i></a>
                        <a href="#" class="btn btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=owners/edit&id=<?= $owner['id'] ?>&view_only=true" title="تعديل"><i class="ti ti-edit"></i></a>
                        <a href="index.php?page=owners/delete&id=<?= $owner['id'] ?>" class="btn btn-icon text-danger confirm-delete" title="حذف"><i class="ti ti-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <?php render_smart_pagination($current_page, $total_pages, $_GET); ?>
    </div>
</div>