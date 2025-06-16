<?php
// =================================================================
// ملف عرض العملاء (clients_view.php) - النسخة النهائية المصححة
// =================================================================

// 1. إعدادات الترقيم والفلترة
// -----------------------------------------------------------------
$records_per_page_options = [10, 25, 50, 100];
$default_records_per_page = 10;

$filter_q = $_GET['q'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_status = $_GET['status'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : $default_records_per_page;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

$branches_for_filter_stmt = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL ORDER BY branch_name ASC");
$branches_for_filter = $branches_for_filter_stmt->fetchAll();

// =================================================================
// 2. بناء الاستعلام الديناميكي
// =================================================================
$sql_where = " WHERE c.deleted_at IS NULL ";
$params = [];

// --- تطبيق فلتر الفروع التلقائي بناءً على صلاحيات المستخدم ---
if (isset($_SESSION['user_branch_ids']) && is_array($_SESSION['user_branch_ids']) && !empty($_SESSION['user_branch_ids'])) {
    $user_branches = $_SESSION['user_branch_ids'];
    $placeholders = implode(',', array_fill(0, count($user_branches), '?'));
    $sql_where .= " AND c.id IN (SELECT client_id FROM client_branches WHERE branch_id IN ({$placeholders})) ";
    foreach ($user_branches as $branch_id) {
        $params[] = $branch_id;
    }
} elseif (isset($_SESSION['user_branch_ids']) && empty($_SESSION['user_branch_ids'])) {
    $sql_where .= " AND 1=0 ";
}

// --- تطبيق الفلاتر التي يختارها المستخدم ---
if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (c.client_name LIKE ? OR c.id_number LIKE ? OR c.tax_number LIKE ? OR c.mobile LIKE ?) ";
    array_push($params, $search_term, $search_term, $search_term, $search_term);
}
if (!empty($filter_type)) { $sql_where .= " AND c.client_type = ? "; $params[] = $filter_type; }
if (!empty($filter_status)) { $sql_where .= " AND c.status = ? "; $params[] = $filter_status; }
if (!empty($filter_branch_id)) {
    $sql_where .= " AND c.id IN (SELECT client_id FROM client_branches WHERE branch_id = ?) ";
    $params[] = $filter_branch_id;
}

// =================================================================
// 3. حساب الإحصائيات والإجمالي
// =================================================================
$stats_sql = "
    SELECT COUNT(c.id) AS total_count,
        SUM(CASE WHEN c.client_type = 'فرد' THEN 1 ELSE 0 END) AS individual_count,
        SUM(CASE WHEN c.client_type = 'منشأة' THEN 1 ELSE 0 END) AS company_count,
        SUM(CASE WHEN c.status = 'نشط' THEN 1 ELSE 0 END) AS active_count
    FROM clients c {$sql_where}
";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute($params);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

$total_records = $stats['total_count'] ?? 0;
$total_pages = ceil($total_records / $limit);

// =================================================================
// 4. جلب سجلات الصفحة الحالية
// =================================================================
$data_sql = "
    SELECT 
        c.*,
        (SELECT COUNT(*) FROM contracts_rental cr WHERE cr.client_id = c.id AND cr.deleted_at IS NULL) as contracts_count,
        (SELECT COUNT(*) FROM client_branches cb WHERE cb.client_id = c.id) as branch_count,
        (SELECT GROUP_CONCAT(b.branch_name SEPARATOR '<br>') FROM branches b JOIN client_branches cb ON b.id = cb.branch_id WHERE cb.client_id = c.id) as branch_names
    FROM clients c
    {$sql_where}
    ORDER BY c.id DESC 
    LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$clients = $data_stmt->fetchAll();

$status_colors = ['نشط' => 'success', 'ملغي' => 'danger'];
?>

<!-- ============================================================= -->
<!-- بداية عرض الواجهة (HTML)                                    -->
<!-- ============================================================= -->

<!-- 1. شريط العنوان والأزرار الرئيسية -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-users ms-2"></i>إدارة العملاء</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <button onclick="window.print();" class="btn btn-sm btn-outline-dark"><i class="fas fa-print ms-1"></i> طباعة</button>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=clients/add&view_only=true" data-bs-title="إضافة عميل جديد">
            <i class="fas fa-plus-circle ms-1"></i>إضافة عميل جديد
        </button>
    </div>
</div>

<!-- 2. نموذج الفرز -->
<div class="card bg-light mb-4">
    <div class="card-body">
        <form action="index.php" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="clients">

            <div class="col-md-4">
                <label for="q" class="form-label">بحث شامل</label>
                <input type="search" class="form-control" name="q" id="q" placeholder="ابحث بالاسم، الهوية، الجوال..." value="<?php echo htmlspecialchars($filter_q ?? ''); ?>">
            </div>

            <div class="col-md-2">
                <label for="branch_id" class="form-label">الفرع</label>
                <select name="branch_id" id="branch_id" class="form-select">
                    <option value="">كل الفروع</option>
                    <?php foreach ($branches_for_filter as $branch): ?>
                        <option value="<?php echo $branch['id']; ?>" <?php echo ($filter_branch_id == $branch['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($branch['branch_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="type" class="form-label">النوع</label>
                <select name="type" id="type" class="form-select">
                    <option value="">الكل</option>
                    <option value="فرد" <?php echo ($filter_type == 'فرد') ? 'selected' : ''; ?>>فرد</option>
                    <option value="منشأة" <?php echo ($filter_type == 'منشأة') ? 'selected' : ''; ?>>منشأة</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">الحالة</label>
                <select name="status" id="status" class="form-select">
                    <option value="">الكل</option>
                    <option value="نشط" <?php echo ($filter_status == 'نشط') ? 'selected' : ''; ?>>نشط</option>
                    <option value="ملغي" <?php echo ($filter_status == 'ملغي') ? 'selected' : ''; ?>>ملغي</option>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search ms-1"></i> تطبيق</button>
                <a href="index.php?page=clients" class="btn btn-secondary w-100 mt-1">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<!-- 3. شريط الإحصائيات -->
<div class="row mb-4 text-center">
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">إجمالي العملاء</h6><p class="card-text fs-4 fw-bold text-primary"><?php echo $stats['total_count'] ?? 0; ?></p></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">عملاء (أفراد)</h6><p class="card-text fs-4 fw-bold text-info"><?php echo $stats['individual_count'] ?? 0; ?></p></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">عملاء (منشآت)</h6><p class="card-text fs-4 fw-bold text-secondary"><?php echo $stats['company_count'] ?? 0; ?></p></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">العملاء النشطين</h6><p class="card-text fs-4 fw-bold text-success"><?php echo $stats['active_count'] ?? 0; ?></p></div></div></div>
</div>

<!-- 4. جدول عرض البيانات -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th style="width: 50px;">م</th>
                <th>#</th>
                <th>الاسم</th>
                <th>النوع</th>
                <th>الفرع</th>
                <th>رقم السجل</th>
                <th>الرقم الضريبي</th>
                <th>الجوال</th>
                <th>العقود</th>
                <th>الحالة</th>
                <th>ملاحظات</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clients)): ?>
                <tr><td colspan="11" class="text-center">لا توجد سجلات تطابق شروط البحث.</td></tr>
            <?php else: ?>
                <?php $row_counter = $offset + 1; ?>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo $row_counter++; ?></td>
                        <td><?php echo $client['id']; ?></td>
                        <td><?php echo htmlspecialchars($client['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($client['client_type']); ?></td>
                        <td>
    <?php if ($client['branch_count'] > 0): ?>
        <button type="button" class="btn btn-dark btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#mainModal"
                data-bs-url="index.php?page=clients/branches_modal&id=<?php echo $client['id']; ?>&view_only=true"
                data-bs-title="الفروع المرتبطة بالعميل: <?php echo htmlspecialchars($client['client_name']); ?>">
            <?php echo $client['branch_count']; ?>
        </button>
    <?php else: ?>
        —
    <?php endif; ?>
</td>
                        <td><?php echo htmlspecialchars($client['id_number'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($client['tax_number'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($client['mobile'] ?? '—'); ?></td>
                        <td><span class="badge bg-dark"><?php echo $client['contracts_count']; ?></span></td>
                        <td><span class="badge bg-<?php echo $status_colors[$client['status']] ?? 'secondary'; ?>"><?php echo htmlspecialchars($client['status']); ?></span></td>
                        <td>
                            <?php if (!empty($client['notes'])): ?>
                                <i class="fas fa-info-circle text-primary" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($client['notes']); ?>"></i>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="print.php?template=client_profile_print&id=<?php echo $client['id']; ?>&view_mode=print" class="btn btn-sm btn-secondary" target="_blank" data-bs-toggle="tooltip" title="طباعة ملف العميل">
                                    <i class="fas fa-id-card"></i>
                                </a>
                                <span class="d-inline-block" data-bs-toggle="tooltip" title="كشف حساب">
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=reports/client_statement_modal&id=<?php echo $client['id']; ?>&view_only=true" data-bs-title="إعداد كشف حساب للعميل: <?php echo htmlspecialchars($client['client_name']); ?>">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </button>
                                </span>
                                <span class="d-inline-block" data-bs-toggle="tooltip" title="تعديل">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=clients/edit&id=<?php echo $client['id']; ?>&view_only=true" data-bs-title="تعديل العميل: <?php echo htmlspecialchars($client['client_name']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </span>
                                <a href="index.php?page=clients/delete&id=<?php echo $client['id']; ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="حذف" onclick="return confirm('سيتم نقل هذا العنصر إلى الأرشيف. هل أنت متأكد؟');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 5. شريط الترقيم -->
<div class="d-flex justify-content-between align-items-center mt-3">
    <?php render_smart_pagination($current_page, $total_pages, $_GET); ?>
    <div class="d-flex align-items-center">
        <span class="ms-2 text-muted">عرض:</span>
        <form action="index.php" method="GET" class="d-inline-block">
            <?php foreach ($_GET as $key => $value): if ($key !== 'limit' && $key !== 'p'): ?><input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>"><?php endif; endforeach; ?>
            <select name="limit" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($records_per_page_options as $option): ?><option value="<?php echo $option; ?>" <?php echo ($limit == $option) ? 'selected' : ''; ?>><?php echo $option; ?></option><?php endforeach; ?>
            </select>
        </form>
        <span class="me-2 text-muted">سجلات</span>
    </div>
</div>