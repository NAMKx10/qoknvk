<?php
// =================================================================
// 1. إعدادات الترقيم والفلترة
// =================================================================
$records_per_page_options = [10, 25, 50, 100];
$default_records_per_page = 10;

$filter_q = $_GET['q'] ?? null;
$filter_property_id = $_GET['property_id'] ?? null;
$filter_unit_type = $_GET['unit_type'] ?? null;
$filter_status = $_GET['status'] ?? null;
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : $default_records_per_page;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

// جلب بيانات الفلاتر الديناميكية
$properties_for_filter_stmt = $pdo->query("SELECT id, property_name FROM properties WHERE deleted_at IS NULL ORDER BY property_name ASC");
$properties_for_filter = $properties_for_filter_stmt->fetchAll();
$unit_types_stmt = $pdo->prepare("SELECT option_value FROM lookup_options WHERE group_key = ? AND group_key != option_key AND deleted_at IS NULL ORDER BY display_order, option_value ASC");
$unit_types_stmt->execute(['unit_type']);
$unit_types_for_filter = $unit_types_stmt->fetchAll(PDO::FETCH_COLUMN);

// =================================================================
// 2. بناء الاستعلام الديناميكي
// =================================================================
$sql_from_joins = "
    FROM units u
    LEFT JOIN properties p ON u.property_id = p.id
    LEFT JOIN contract_units cu ON u.id = cu.unit_id AND cu.id = (SELECT MAX(id) FROM contract_units WHERE unit_id = u.id) -- للحصول على آخر عقد فقط
    LEFT JOIN contracts_rental cr ON cu.contract_id = cr.id AND cr.status = 'نشط' AND cr.deleted_at IS NULL
    LEFT JOIN clients c ON cr.client_id = c.id
";
$sql_where = " WHERE u.deleted_at IS NULL ";
$params = [];

// --- تطبيق فلتر الفروع التلقائي بناءً على صلاحيات المستخدم ---
$sql_where .= build_branches_query_condition('p', $params);

if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (u.unit_name LIKE ? OR u.unit_code LIKE ? OR p.property_name LIKE ? OR c.client_name LIKE ?) ";
    array_push($params, $search_term, $search_term, $search_term, $search_term);
}
if (!empty($filter_property_id)) { $sql_where .= " AND u.property_id = ? "; $params[] = $filter_property_id; }
if (!empty($filter_unit_type)) { $sql_where .= " AND u.unit_type = ? "; $params[] = $filter_unit_type; }
if (!empty($filter_status)) { $sql_where .= " AND u.status = ? "; $params[] = $filter_status; }

// =================================================================
// 3. حساب الإحصائيات والإجمالي
// =================================================================
$stats_sql = "
    SELECT COUNT(DISTINCT u.id) AS total_count,
           SUM(CASE WHEN u.status = 'مؤجرة' THEN 1 ELSE 0 END) AS rented_count,
           SUM(CASE WHEN u.status = 'متاحة' THEN 1 ELSE 0 END) AS available_count,
           SUM(u.area) AS total_area
    " . $sql_from_joins . $sql_where;
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute($params);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

$total_records = $stats['total_count'] ?? 0;
$total_pages = ceil($total_records / $limit);

// =================================================================
// 4. جلب سجلات الصفحة الحالية
// =================================================================
$data_sql = "
    SELECT u.*, p.property_name, c.client_name
    " . $sql_from_joins . $sql_where . "
    GROUP BY u.id 
    ORDER BY u.id DESC 
    LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$units = $data_stmt->fetchAll();

$status_colors = ['متاحة' => 'success', 'مؤجرة' => 'warning', 'ملغاة' => 'danger'];
?>

<!-- ============================================================= -->
<!-- بداية عرض الواجهة (HTML)                                    -->
<!-- ============================================================= -->

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-door-closed ms-2"></i>إدارة الوحدات</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <button onclick="window.print();" class="btn btn-sm btn-outline-dark"><i class="fas fa-print ms-1"></i> طباعة</button>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=units/add&view_only=true" data-bs-title="إضافة وحدة جديدة">
            <i class="fas fa-plus-circle ms-1"></i>إضافة وحدة جديدة
        </button>
    </div>
</div>

<!-- نموذج الفرز والبحث (مطور) -->
<div class="card bg-light mb-4">
    <div class="card-body">
        <form action="index.php" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="units">
            <div class="col-md-3"><label for="q" class="form-label">بحث شامل</label><input type="search" class="form-control" name="q" id="q" placeholder="ابحث بالاسم، الكود، العقار، المستأجر..." value="<?php echo htmlspecialchars($filter_q ?? ''); ?>"></div>
            <div class="col-md-3"><label for="property_id" class="form-label">فرز حسب العقار</label><select name="property_id" id="property_id" class="form-select"><option value="">كل العقارات</option><?php foreach ($properties_for_filter as $property): ?><option value="<?php echo $property['id']; ?>" <?php echo ($filter_property_id == $property['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($property['property_name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label for="unit_type" class="form-label">فرز حسب النوع</label><select name="unit_type" id="unit_type" class="form-select"><option value="">كل الأنواع</option><?php foreach ($unit_types_for_filter as $type): ?><option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($filter_unit_type == $type) ? 'selected' : ''; ?>><?php echo htmlspecialchars($type); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label for="status" class="form-label">فرز حسب الحالة</label><select name="status" id="status" class="form-select"><option value="">كل الحالات</option><option value="متاحة" <?php echo ($filter_status == 'متاحة') ? 'selected' : ''; ?>>متاحة</option><option value="مؤجرة" <?php echo ($filter_status == 'مؤجرة') ? 'selected' : ''; ?>>مؤجرة</option><option value="ملغاة" <?php echo ($filter_status == 'ملغاة') ? 'selected' : ''; ?>>ملغاة</option></select></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search ms-1"></i> تطبيق</button><a href="index.php?page=units" class="btn btn-secondary w-100 mt-1">إلغاء</a></div>
        </form>
    </div>
</div>

<!-- شريط الإحصائيات (دقيق) -->
<div class="row mb-4 text-center">
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">إجمالي الوحدات</h6><p class="card-text fs-4 fw-bold text-primary"><?php echo $stats['total_count'] ?? 0; ?></p></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">وحدات متاحة</h6><p class="card-text fs-4 fw-bold text-success"><?php echo $stats['available_count'] ?? 0; ?></p></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">وحدات مؤجرة</h6><p class="card-text fs-4 fw-bold text-warning"><?php echo $stats['rented_count'] ?? 0; ?></p></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">إجمالي المساحة</h6><p class="card-text fs-4 fw-bold"><?php echo number_format($stats['total_area'] ?? 0, 2); ?> <small>م²</small></p></div></div></div>
</div>

<!-- جدول عرض البيانات (مطابق 100% للتصميم الأصلي) -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th style="width: 50px;">م</th>
                <th>#</th>
                <th>كود الوحدة</th>
                <th>اسم الوحدة</th>
                <th>نوع الوحدة</th>
                <th>العقار</th>
                <th>المساحة</th>
                <th>الحالة</th>
                <th>المستأجر الحالي</th>
                <th>الملاحظات</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($units)): ?>
                <tr><td colspan="11" class="text-center">لا توجد وحدات تطابق شروط البحث.</td></tr>
            <?php else: ?>
                <?php $row_counter = $offset + 1; ?>
                <?php foreach ($units as $unit): ?>
                    <tr>
                        <td><?php echo $row_counter++; ?></td>
                        <td><?php echo $unit['id']; ?></td>
                        <td><?php echo htmlspecialchars($unit['unit_code'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($unit['unit_name']); ?></td>
                        <td><?php echo htmlspecialchars($unit['unit_type']); ?></td>
                        <td><?php echo htmlspecialchars($unit['property_name']); ?></td>
                        <td><?php echo number_format($unit['area'] ?? 0, 2); ?></td>
                        <td><span class="badge bg-<?php echo $status_colors[$unit['status']] ?? 'secondary'; ?>"><?php echo htmlspecialchars($unit['status']); ?></span></td>
                        <td><?php echo htmlspecialchars($unit['client_name'] ?? '—'); ?></td>
                        <td>
                            <?php if (!empty($unit['notes'])): ?>
                                <i class="fas fa-info-circle text-primary" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($unit['notes']); ?>"></i>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="print.php?template=unit_profile_print&id=<?php echo $unit['id']; ?>&view_mode=print" target="_blank" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="طباعة ملف الوحدة"><i class="fas fa-id-card"></i></a>
                                <span class="d-inline-block" data-bs-toggle="tooltip" title="تعديل">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=units/edit&id=<?php echo $unit['id']; ?>&view_only=true" data-bs-title="تعديل الوحدة: <?php echo htmlspecialchars($unit['unit_name']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </span>
                                <a href="index.php?page=units/delete&id=<?php echo $unit['id']; ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="حذف" onclick="return confirm('سيتم نقل هذا العنصر إلى الأرشيف. هل أنت متأكد؟');"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- شريط الترقيم -->
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