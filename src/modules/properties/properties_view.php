<?php
// =================================================================
// PHP - v3.0 (Full-featured & Corrected)
// =================================================================
$limit = 10; $current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1; $offset = ($current_page - 1) * $limit;
$filter_q = $_GET['q'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_ownership = $_GET['ownership'] ?? null;

$sql_where = " WHERE p.deleted_at IS NULL ";
$params = [];
$sql_where .= build_branches_query_condition('p', $params);

if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (p.property_name LIKE ? OR p.property_code LIKE ? OR p.owner_name LIKE ?) ";
    array_push($params, $search_term, $search_term, $search_term);
}
if (!empty($filter_branch_id)) { $sql_where .= " AND p.branch_id = ? "; $params[] = $filter_branch_id; }
if (!empty($filter_type)) { $sql_where .= " AND p.property_type = ? "; $params[] = $filter_type; }
if (!empty($filter_ownership)) { $sql_where .= " AND p.ownership_type = ? "; $params[] = $filter_ownership; }

// Fetch Stats
$stats_sql = "SELECT COUNT(p.id) AS total_count, SUM(p.area) AS total_area, (SELECT COUNT(u.id) FROM units u JOIN properties p_join ON u.property_id = p_join.id WHERE p_join.id = p.id) as total_units FROM properties p {$sql_where}";
$stats_stmt = $pdo->prepare(str_replace('p.id', '(SELECT 1)', $stats_sql));
$stats_stmt->execute($params);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch Data
$total_records = $stats['total_count'] ?? 0;
$total_pages = ceil($total_records / $limit);
$data_sql = "SELECT p.*, b.branch_code, (SELECT COUNT(*) FROM units u WHERE u.property_id = p.id AND u.deleted_at IS NULL) as units_count FROM properties p LEFT JOIN branches b ON p.branch_id = b.id {$sql_where} ORDER BY p.id DESC LIMIT {$limit} OFFSET {$offset}";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$properties = $data_stmt->fetchAll();

// Data for Filters
$branches_for_filter = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();
$property_types_for_filter = $pdo->query("SELECT DISTINCT property_type FROM properties WHERE deleted_at IS NULL AND property_type IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- HTML - v3.0 (Full-featured with improved UI) -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">إدارة العقارات</h2>
                <div class="text-muted mt-1"><?= $total_records ?> عقار</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=properties/add&view_only=true" data-bs-title="إضافة عقار جديد"><i class="ti ti-plus me-2"></i>إضافة عقار</a>
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row row-cards mb-4">
    <div class="col-md-4"><div class="card card-sm"><div class="card-body"><div class="row align-items-center"><div class="col-auto"><span class="bg-primary text-white avatar"><i class="ti ti-building-arch"></i></span></div><div class="col"><div class="font-weight-medium">إجمالي العقارات</div><div class="text-muted"><?= $stats['total_count'] ?? 0 ?></div></div></div></div></div></div>
    <div class="col-md-4"><div class="card card-sm"><div class="card-body"><div class="row align-items-center"><div class="col-auto"><span class="bg-green text-white avatar"><i class="ti ti-door"></i></span></div><div class="col"><div class="font-weight-medium">إجمالي الوحدات</div><div class="text-muted"><?= $stats['total_units'] ?? 0 ?></div></div></div></div></div></div>
    <div class="col-md-4"><div class="card card-sm"><div class="card-body"><div class="row align-items-center"><div class="col-auto"><span class="bg-azure text-white avatar"><i class="ti ti-ruler-measure"></i></span></div><div class="col"><div class="font-weight-medium">إجمالي المساحة</div><div class="text-muted"><?= number_format($stats['total_area'] ?? 0, 2) ?> م²</div></div></div></div></div></div>
</div>

<!-- Filtering and Search Card -->
<div class="card card-body mb-4">
    <form action="index.php" method="GET">
        <input type="hidden" name="page" value="properties">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">بحث شامل</label><input type="search" name="q" class="form-control" placeholder="ابحث بالاسم، الكود، المالك..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
            <div class="col-md-2"><label class="form-label">الفرع</label><select class="form-select" name="branch_id"><option value="">الكل</option><?php foreach($branches_for_filter as $branch):?><option value="<?=$branch['id']?>" <?= ($filter_branch_id == $branch['id']) ? 'selected' : '' ?>><?=htmlspecialchars($branch['branch_name'])?></option><?php endforeach;?></select></div>
            <div class="col-md-2"><label class="form-label">النوع</label><select class="form-select" name="type"><option value="">الكل</option><?php foreach($property_types_for_filter as $type):?><option value="<?=$type?>" <?= ($filter_type == $type) ? 'selected' : '' ?>><?=htmlspecialchars($type)?></option><?php endforeach;?></select></div>
            <div class="col-md-2"><label class="form-label">التملك</label><select class="form-select" name="ownership"><option value="">الكل</option><option value="ملك" <?= ($filter_ownership == 'ملك')?'selected':'' ?>>ملك</option><option value="استثمار" <?= ($filter_ownership == 'استثمار')?'selected':'' ?>>استثمار</option></select></div>
            <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">تطبيق</button><a href="index.php?page=properties" class="btn btn-ghost-secondary w-100 ms-2">إلغاء</a></div>
        </div>
    </form>
</div>

<!-- Main Data Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap">
            <thead>
                <tr>
                    <th>م</th><th>الكود</th><th>اسم العقار</th><th>الفرع</th><th>النوع</th><th>التملك</th><th>المساحة</th><th>الوحدات</th><th>الحالة</th><th>ملاحظات</th><th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($properties)): ?><tr><td colspan="11" class="text-center">لا توجد نتائج.</td></tr><?php else: $row_counter = $offset + 1; foreach($properties as $property): ?>
                <tr>
                    <td><span class="text-muted"><?= $row_counter++ ?></span></td>
                    <td><?= htmlspecialchars($property['property_code'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($property['property_name']) ?></td>
                    <td><span class="badge bg-secondary-lt"><?= htmlspecialchars($property['branch_code'] ?? '—') ?></span></td>
                    <td><?= htmlspecialchars($property['property_type'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($property['ownership_type'] ?? '—') ?></td>
                    <td><?= number_format($property['area'] ?? 0, 2) ?> م²</td>
                    <td><?= $property['units_count'] ?></td>
                    <td><span class="badge bg-<?= ($property['status'] === 'نشط') ? 'success' : 'danger' ?>-lt"><?= htmlspecialchars($property['status']) ?></span></td>
                    <td><?php if (!empty($property['notes'])): ?><i class="ti ti-info-circle text-primary" data-bs-toggle="tooltip" title="<?= htmlspecialchars($property['notes']) ?>"></i><?php endif; ?></td>
                    <td class="text-end">
                        <a href="print.php?template=property_profile_print&id=<?= $property['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank">طباعة</a>
                        <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=properties/edit&id=<?= $property['id'] ?>&view_only=true" data-bs-title="تعديل العقار">تعديل</a>
                        <a href="index.php?page=properties/delete&id=<?= $property['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete">حذف</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_pages > 1): ?>
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-muted">عرض <span><?= $offset + 1 ?></span> إلى <span><?= min($offset + $limit, $total_records) ?></span> من <span><?= $total_records ?></span> مدخلات</p>
        <ul class="pagination m-0 ms-auto">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?><li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>"><a class="page-link" href="?page=properties&p=<?= $i ?>&q=<?= urlencode($filter_q) ?>&branch_id=<?= urlencode($filter_branch_id) ?>&type=<?= urlencode($filter_type) ?>&ownership=<?= urlencode($filter_ownership) ?>"><?= $i ?></a></li><?php endfor; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>