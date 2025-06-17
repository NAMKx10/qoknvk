<?php
// =================================================================
// PHP - v4.0 (Final Blueprint Version)
// =================================================================
// 1. Settings & Filters
$records_per_page_options = [5, 10, 20, 50];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : 10; 
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;
$filter_q = $_GET['q'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;
$filter_type = $_GET['type'] ?? null;

// 2. Build Query
$sql_from = "FROM properties p LEFT JOIN branches b ON p.branch_id = b.id";
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

// 3. Fetch Stats & Data
// We create a temporary params array for stats to avoid issues
$stats_params = $params;
$stats_sql = "SELECT COUNT(p.id) as total_count, (SELECT COUNT(u.id) FROM units u WHERE u.property_id = p.id AND u.deleted_at IS NULL) as total_units FROM properties p {$sql_where}";
$stats_stmt = $pdo->prepare(str_replace('p.id', '(SELECT 1)', $stats_sql));
$stats_stmt->execute($stats_params);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

$total_records = $stats['total_count'] ?? 0;
$total_pages = ceil($total_records / $limit);

$data_sql = "SELECT p.*, b.branch_code, (SELECT COUNT(*) FROM units u WHERE u.property_id = p.id AND u.deleted_at IS NULL) as units_count {$sql_from} {$sql_where} ORDER BY p.id DESC LIMIT {$limit} OFFSET {$offset}";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$properties = $data_stmt->fetchAll();

$branches_for_filter = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();
$property_types_for_filter = $pdo->query("SELECT DISTINCT property_type FROM properties WHERE deleted_at IS NULL AND property_type IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- HTML - v4.0 -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">إدارة العقارات</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" class="btn"><i class="ti ti-table-plus me-2"></i>إدخال متعدد</a>
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=properties/add&view_only=true" data-bs-title="إضافة عقار جديد">
                        <i class="ti ti-plus me-2"></i>إضافة عقار
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-4">
    <!-- Stat Cards Here -->
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة العقارات</h3>
        <div class="ms-auto text-muted">
            <form action="index.php" method="GET" class="d-flex">
                <input type="hidden" name="page" value="properties">
                <div class="ms-2 d-inline-block"><input type="search" name="q" class="form-control form-control-sm" placeholder="بحث..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
                <div class="ms-2 d-inline-block"><select class="form-select form-select-sm" name="branch_id"><option value="">كل الفروع</option><?php foreach($branches_for_filter as $branch):?><option value="<?=$branch['id']?>" <?= ($filter_branch_id == $branch['id']) ? 'selected' : '' ?>><?=htmlspecialchars($branch['branch_name'])?></option><?php endforeach;?></select></div>
                <button type="submit" class="btn btn-sm btn-primary ms-2">تطبيق</button>
            </form>
        </div>
    </div>
    <div class="card-body border-bottom py-3">
        <div class="d-flex">
            <div class="text-muted">
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">إجراءات جماعية</button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">تغيير الحالة</a>
                        <a class="dropdown-item" href="#">نقل إلى الأرشيف</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap">
            <thead>
                <tr>
                    <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox"></th>
                    <th class="w-1">م.</th>
                    <th>صورة</th>
                    <th>العقار / الكود</th>
                    <th>الفرع</th>
                    <th>النوع</th>
                    <th>التملك</th>
                    <th>المالك</th>
                    <th>المدينة</th>
                    <th>الحالة</th>
                    <th>ملاحظات</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($properties)): ?><tr><td colspan="12" class="text-center">لا توجد نتائج.</td></tr><?php else: $row_counter = $offset + 1; foreach($properties as $property): ?>
                <tr>
                    <td><input class="form-check-input m-0 align-middle" type="checkbox"></td>
                    <td><span class="text-muted"><?= $row_counter++ ?></span></td>
                    <td><span class="avatar" style="background-image: url(./assets/static/properties/default.jpg)"></span></td>
                    <td><div><?= htmlspecialchars($property['property_name']) ?></div><div class="text-muted"><?= htmlspecialchars($property['property_code'] ?? '—') ?></div></td>
                    <td><span class="badge bg-secondary-lt"><?= htmlspecialchars($property['branch_code'] ?? '—') ?></span></td>
                    <td><?= htmlspecialchars($property['property_type'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($property['ownership_type'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($property['owner_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($property['city'] ?? '—') ?></td>
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
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-muted">عرض <span><?= $offset + 1 ?></span> إلى <span><?= min($offset + $limit, $total_records) ?></span> من <span><?= $total_records ?></span> مدخلات</p>
        <ul class="pagination m-0 ms-auto">
            <!-- Pagination links here -->
        </ul>
    </div>
</div>