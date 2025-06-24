<?php
// src/modules/branches/branches_view.php
// (النسخة النهائية - مطابقة 100% للنموذج القياسي للعقارات + كل طلباتك)

// 1. PHP Logic
$records_per_page_options = [10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : 10;
$current_page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

// --- الفلاتر ---
$filter_q = $_GET['q'] ?? null;
$filter_type = $_GET['type'] ?? null;

// --- بناء الاستعلام ---
$sql_where = " WHERE b.deleted_at IS NULL ";
$params = [];
if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (b.branch_name LIKE ? OR b.branch_code LIKE ? OR b.registration_number LIKE ?)";
    array_push($params, $search_term, $search_term, $search_term);
}
if (!empty($filter_type)) { $sql_where .= " AND b.branch_type = ? "; $params[] = $filter_type; }

// --- جلب الإحصائيات (4 بطاقات) ---
$stats_query = "SELECT COUNT(id) as total,
    SUM(CASE WHEN status='Active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN branch_type='منشأة' THEN 1 ELSE 0 END) as companies,
    SUM(CASE WHEN branch_type='فرد' THEN 1 ELSE 0 END) as individuals
FROM branches WHERE deleted_at IS NULL";
$stats = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC);

// --- جلب البيانات ---
$data_sql = "
    SELECT b.*,
        (SELECT COUNT(id) FROM properties WHERE branch_id = b.id AND deleted_at IS NULL) as properties_count,
        (SELECT COUNT(u.id) FROM units u JOIN properties p ON u.property_id = p.id WHERE p.branch_id = b.id AND u.deleted_at IS NULL) as units_count
    FROM branches b {$sql_where} ORDER BY b.id DESC LIMIT {$limit} OFFSET {$offset}";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$branches = $data_stmt->fetchAll();

$total_records_stmt = $pdo->prepare("SELECT COUNT(*) FROM branches b {$sql_where}");
$total_records_stmt->execute($params);
$total_records = $total_records_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// --- بيانات الفلاتر ---
$branch_types_filter = $pdo->query("SELECT option_value FROM lookup_options WHERE group_key = 'entity_type' AND option_key != 'entity_type' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_COLUMN);
$statuses_map_stmt = $pdo->query("SELECT option_key, option_value, bg_color FROM lookup_options WHERE group_key = 'status'");
$statuses_map = [];
foreach ($statuses_map_stmt as $row) { $statuses_map[$row['option_key']] = ['name' => $row['option_value'], 'bg_color' => $row['bg_color']]; }
?>

<!-- === HTML المطابق 100% لتصميم العقارات === -->
<div class="card">
    <div class="card-body">
        <!-- 1. صف العنوان والأزرار العلوية -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h2 class="page-title mb-0">إدارة الفروع والكيانات</h2></div>
            <div class="btn-list">
                <button onclick="window.print();" class="btn btn-outline-secondary"><i class="ti ti-printer me-2"></i>طباعة</button>
                <a href="#" class="btn"><i class="ti ti-table-plus me-2"></i>إدخال متعدد</a>
                <a href="#" class="btn"><i class="ti ti-upload me-2"></i>إجراءات متعددة</a>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=branches/add&view_only=true"><i class="ti ti-plus me-2"></i>إضافة فرع جديد</a>
            </div>
        </div>

        <!-- 2. بطاقات الإحصائيات (Ribbon Style) -->
        <div class="row row-cards mb-4">
            <div class="col-md-3"><div class="card bg-primary text-primary-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-primary"><i class="ti ti-building-community"></i></div></div><div class="card-body"><h3 class="card-title m-0">إجمالي الفروع</h3><p class="h1 mt-1 mb-0"><?= $stats['total'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-success text-success-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-success"><i class="ti ti-circle-check"></i></div></div><div class="card-body"><h3 class="card-title m-0">الفروع النشطة</h3><p class="h1 mt-1 mb-0"><?= $stats['active'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-info text-info-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-info"><i class="ti ti-building-skyscraper"></i></div></div><div class="card-body"><h3 class="card-title m-0">كيانات (منشأة)</h3><p class="h1 mt-1 mb-0"><?= $stats['companies'] ?? 0 ?></p></div></div></div>
            <div class="col-md-3"><div class="card bg-warning text-warning-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-warning"><i class="ti ti-user-circle"></i></div></div><div class="card-body"><h3 class="card-title m-0">كيانات (فرد)</h3><p class="h1 mt-1 mb-0"><?= $stats['individuals'] ?? 0 ?></p></div></div></div>
        </div>

        <!-- 3. قسم الفلترة -->
        <form action="index.php" method="GET"><input type="hidden" name="page" value="branches">
            <div class="row g-3"><div class="col-md-8"><input type="search" name="q" class="form-control" placeholder="ابحث بالاسم، الكود، السجل..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
                <div class="col-md-2"><select class="form-select" name="type"><option value="">كل الأنواع</option><?php foreach($branch_types_filter as $type):?><option value="<?= htmlspecialchars($type) ?>" <?= ($filter_type == $type)?'selected':''?>><?= htmlspecialchars($type) ?></option><?php endforeach;?></select></div>
                <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">بحث</button><a href="index.php?page=branches" class="btn btn-ghost-secondary ms-2" title="إعادة تعيين"><i class="ti ti-refresh"></i></a></div>
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
                    <th>صورة</th><th>الفرع/الشركة</th><th>البيانات الرئيسية</th><th>معلومات التواصل</th><th>نوع الكيان</th><th>الحالة</th><th>عدد العقارات/الوحدات</th><th>ملاحظات</th><th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($branches)): ?><tr><td colspan="10" class="text-center p-4">لا توجد نتائج.</td></tr><?php else: foreach ($branches as $branch): ?>
                <tr>
                    <td><input class="form-check-input m-0 align-middle" type="checkbox" name="row_id[]" value="<?= $branch['id'] ?>"></td>
                    <td><span class="avatar" style="background-image: url(./assets/static/avatars/default-branch.svg)"></span></td>
                    <td><div class="fw-bold"><?= htmlspecialchars($branch['branch_name']) ?></div><div class="text-muted">كود: <?= htmlspecialchars($branch['branch_code'] ?? 'N/A') ?></div></td>
                    <td><div class="fw-bold">السجل: <?= htmlspecialchars($branch['registration_number'] ?? '—') ?></div><div class="text-muted">الضريبي: <?= htmlspecialchars($branch['tax_number'] ?? '—') ?></div></td>
                    <td><div class="fw-bold">الجوال: <?= htmlspecialchars($branch['phone'] ?? '—') ?></div><div class="text-muted">الإيميل: <?= htmlspecialchars($branch['email'] ? substr($branch['email'], 0, 20).'...' : '—') ?></div></td>
                    <td><?= htmlspecialchars($branch['branch_type']) ?></td>
                    <td>
    <?php 
        $status_key = $branch['status'];
        $status_name = $statuses_map[$status_key]['name'] ?? $status_key;
        $status_color = $statuses_map[$status_key]['bg_color'] ?? '#6c757d';
    ?>
    <span class="badge" style="background-color: <?= htmlspecialchars($status_color) ?>; color: #fff;">
        <?= htmlspecialchars($status_name) ?>
    </span>
</td>
                    <td><div class="fw-bold">العقارات: <?= $branch['properties_count'] ?></div><div class="text-muted">الوحدات: <?= $branch['units_count'] ?></div></td>
                    <td><?php if (!empty($branch['notes'])): ?><i class="ti ti-info-circle text-primary" data-bs-toggle="tooltip" title="<?= htmlspecialchars($branch['notes']) ?>"></i><?php endif; ?></td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="print.php?template=branch_profile_print&id=<?= $branch['id'] ?>" class="btn btn-icon btn-outline-secondary" target="_blank" data-bs-toggle="tooltip" title="طباعة ملف الفرع"><i class="ti ti-printer"></i></a>
                            <a href="#" class="btn btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=branches/edit&id=<?= $branch['id'] ?>&view_only=true" title="تعديل"><i class="ti ti-edit"></i></a>
                            <a href="index.php?page=branches/delete&id=<?= $branch['id'] ?>" class="btn btn-icon btn-outline-danger confirm-delete" title="حذف (أرشفة)"><i class="ti ti-trash"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
    <div class="text-muted">
        عرض
        <div class="mx-2 d-inline-block">
            <form action="index.php" method="GET" class="d-inline-block" id="limit-form">
                <input type="hidden" name="page" value="branches">
                <!-- الحفاظ على كل قيم الفلترة الأخرى عند تغيير الحد -->
                <input type="hidden" name="q" value="<?= htmlspecialchars($filter_q ?? '') ?>">
                <input type="hidden" name="type" value="<?= htmlspecialchars($filter_type ?? '') ?>">
                <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status ?? '') ?>">
                
                <select name="limit" class="form-select form-select-sm" onchange="document.getElementById('limit-form').submit();">
                    <?php foreach($records_per_page_options as $option): ?>
                        <option value="<?= $option ?>" <?= ($limit == $option) ? 'selected' : '' ?>>
                            <?= $option ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        سجلات
    </div>
    <div class="m-auto">
        <?php render_smart_pagination($current_page, $total_pages, $_GET); ?>
    </div>
    <p class="m-0 text-muted">من أصل <?= $total_records ?> سجل</p>
</div>
</div>