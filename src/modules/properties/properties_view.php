<?php
// =================================================================
// PHP - v2.0 (Full-featured Logic)
// =================================================================
// 1. Settings
$limit = 10; 
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

// 2. Filters
$filter_q = $_GET['q'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;

// 3. Build Query
$sql_from = "FROM properties p LEFT JOIN branches b ON p.branch_id = b.id";
$sql_where = " WHERE p.deleted_at IS NULL ";
$params = [];

// Auto-filter by user's allowed branches
$sql_where .= build_branches_query_condition('p', $params);

// User-selected filters
if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (p.property_name LIKE ? OR p.property_code LIKE ? OR p.owner_name LIKE ? OR b.branch_name LIKE ?) ";
    array_push($params, $search_term, $search_term, $search_term, $search_term);
}
if (!empty($filter_branch_id)) {
    $sql_where .= " AND p.branch_id = ? ";
    $params[] = $filter_branch_id;
}

// 4. Fetch Data & Stats
// Total records
$total_records_stmt = $pdo->prepare("SELECT COUNT(p.id) {$sql_from} {$sql_where}");
$total_records_stmt->execute($params);
$total_records = $total_records_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Data for current page
$data_sql = "SELECT p.*, b.branch_code, (SELECT COUNT(*) FROM units u WHERE u.property_id = p.id AND u.deleted_at IS NULL) as units_count {$sql_from} {$sql_where} ORDER BY p.id DESC LIMIT {$limit} OFFSET {$offset}";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$properties = $data_stmt->fetchAll();

// Branches for filter dropdown
$branches_for_filter_stmt = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL ORDER BY branch_name ASC");
$branches_for_filter = $branches_for_filter_stmt->fetchAll();
?>

<!-- ============================================================= -->
<!-- HTML - v2.0 (Tabler Full Components)                        -->
<!-- ============================================================= -->

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">إدارة العقارات</h2>
                <div class="text-muted mt-1"><?= $total_records ?> عقار</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=properties/add&view_only=true" data-bs-title="إضافة عقار جديد">
                        <i class="ti ti-plus me-2"></i>إضافة عقار
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body border-bottom py-3">
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="properties">
            <div class="d-flex">
                <div class="text-muted">
                    <select class="form-select" name="branch_id" onchange="this.form.submit()">
                        <option value="">كل الفروع</option>
                        <?php foreach ($branches_for_filter as $branch): ?>
                            <option value="<?= $branch['id'] ?>" <?= ($filter_branch_id == $branch['id']) ? 'selected' : '' ?>><?= htmlspecialchars($branch['branch_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ms-auto text-muted">
                    <div class="ms-2 d-inline-block">
                        <input type="search" name="q" class="form-control" placeholder="بحث..." value="<?= htmlspecialchars($filter_q ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">بحث</button>
                </div>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap">
            <thead>
                <tr>
                    <th>الكود</th>
                    <th>اسم العقار</th>
                    <th>الفرع</th>
                    <th>المالك</th>
                    <th>رقم الصك</th>
                    <th>عدد الوحدات</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($properties)): ?>
                    <tr><td colspan="8" class="text-center">لا توجد نتائج.</td></tr>
                <?php else: foreach($properties as $property): ?>
                <tr>
                    <td><span class="text-muted"><?= htmlspecialchars($property['property_code'] ?? 'N/A') ?></span></td>
                    <td><?= htmlspecialchars($property['property_name']) ?></td>
                    <td><span class="badge bg-secondary-lt"><?= htmlspecialchars($property['branch_code'] ?? '—') ?></span></td>
                    <td><?= htmlspecialchars($property['owner_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($property['deed_number'] ?? '—') ?></td>
                    <td><?= $property['units_count'] ?></td>
                    <td><span class="badge bg-<?= ($property['status'] === 'نشط') ? 'success' : 'danger' ?>-lt"><?= htmlspecialchars($property['status']) ?></span></td>
                    <td class="text-end">
                        <a href="print.php?template=property_profile_print&id=<?= $property['id'] ?>" class="btn btn-sm" target="_blank">طباعة</a>
                        <a href="#" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=properties/edit&id=<?= $property['id'] ?>&view_only=true" data-bs-title="تعديل العقار">تعديل</a>
                        <a href="index.php?page=properties/delete&id=<?= $property['id'] ?>" class="btn btn-sm btn-danger confirm-delete">حذف</a>
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
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>"><a class="page-link" href="?page=properties&p=<?= $i ?>&q=<?= urlencode($filter_q) ?>&branch_id=<?= urlencode($filter_branch_id) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>