<?php
// =================================================================
// 1. PHP Logic (Data Fetching & Filtering)
// =================================================================
$filter_q = $_GET['q'] ?? null;
$filter_status = $_GET['status'] ?? null;
$limit = 10; 
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

$sql_where = " WHERE deleted_at IS NULL ";
$params = [];
if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (branch_name LIKE ? OR branch_code LIKE ? OR registration_number LIKE ?) ";
    array_push($params, $search_term, $search_term, $search_term);
}
if (!empty($filter_status)) {
    $sql_where .= " AND status = ? ";
    $params[] = $filter_status;
}

$total_records_stmt = $pdo->prepare("SELECT COUNT(*) FROM branches {$sql_where}");
$total_records_stmt->execute($params);
$total_records = $total_records_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

$data_sql = "SELECT * FROM branches {$sql_where} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$branches = $data_stmt->fetchAll();
?>

<!-- ============================================================= -->
<!-- HTML (Tabler Components)                                    -->
<!-- ============================================================= -->

<!-- 1. Page Header -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">إدارة الفروع</h2>
                <div class="text-muted mt-1"><?= $total_records ?> فرع</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=branches/add&view_only=true" data-bs-title="إضافة فرع جديد">
                    <i class="ti ti-plus me-2"></i>إضافة فرع
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 2. Main Page Content -->
<div class="card">
    <!-- Filtering & Search Form -->
    <div class="card-body border-bottom py-3">
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="branches">
            <div class="d-flex">
                <div class="text-muted">
                    <select class="form-select" name="status" onchange="this.form.submit()">
                        <option value="">كل الحالات</option>
                        <option value="نشط" <?= ($filter_status == 'نشط') ? 'selected' : '' ?>>نشط</option>
                        <option value="ملغي" <?= ($filter_status == 'ملغي') ? 'selected' : '' ?>>ملغي</option>
                    </select>
                </div>
                <div class="ms-auto text-muted">
                    <div class="ms-2 d-inline-block">
                        <input type="search" name="q" class="form-control" aria-label="Search" placeholder="بحث..." value="<?= htmlspecialchars($filter_q ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">بحث</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Branches Table -->
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap">
            <thead>
                <tr>
                    <th>الاسم / الكود</th>
                    <th>البيانات الرئيسية</th>
                    <th>الحالة</th>
                    <th>تاريخ الإنشاء</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($branches)): ?>
                    <tr><td colspan="5" class="text-center">لا توجد نتائج.</td></tr>
                <?php else: foreach($branches as $branch): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="avatar me-2" style="background-image: url(./assets/static/avatars/default-branch.svg)"></span>
                            <div class="flex-fill">
                                <div class="font-weight-medium"><?= htmlspecialchars($branch['branch_name']) ?></div>
                                <div class="text-muted"><?= htmlspecialchars($branch['branch_code'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>السجل: <?= htmlspecialchars($branch['registration_number'] ?? '—') ?></div>
                        <div class="text-muted">الجوال: <?= htmlspecialchars($branch['phone'] ?? '—') ?></div>
                    </td>
                    <td><span class="badge bg-<?= ($branch['status'] === 'نشط') ? 'success' : 'danger' ?>-lt"><?= htmlspecialchars($branch['status']) ?></span></td>
                    <td><?= date('Y-m-d', strtotime($branch['created_at'])) ?></td>
                    <td class="text-end">
                        <a href="#" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=branches/edit&id=<?= $branch['id'] ?>&view_only=true" data-bs-title="تعديل الفرع: <?= htmlspecialchars($branch['branch_name']) ?>">تعديل</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-muted">عرض <span><?= $offset + 1 ?></span> إلى <span><?= min($offset + $limit, $total_records) ?></span> من <span><?= $total_records ?></span> مدخلات</p>
        <ul class="pagination m-0 ms-auto">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=branches&p=<?= $i ?>&q=<?= urlencode($filter_q) ?>&status=<?= urlencode($filter_status) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

<!-- Modal (النافذة المنبثقة) -->
<div class="modal modal-blur fade" id="main-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- المحتوى سيتم تحميله هنا عبر AJAX -->
            </div>
        </div>
    </div>
</div>
