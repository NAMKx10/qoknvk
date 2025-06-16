<?php
// =================================================================
// ملف عرض العقارات (properties_view.php) - النسخة النهائية المصححة
// =================================================================

// 1. إعدادات الترقيم والفلترة
// -----------------------------------------------------------------
$records_per_page_options = [10, 25, 50, 100];
$default_records_per_page = 10;

$filter_q = $_GET['q'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_ownership = $_GET['ownership'] ?? null;
$filter_status = $_GET['status'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : $default_records_per_page;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

// جلب الفروع للفلترة
$branches_for_filter_stmt = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL ORDER BY branch_name ASC");
$branches_for_filter = $branches_for_filter_stmt->fetchAll();
$property_types_stmt = $pdo->prepare("SELECT option_value FROM lookup_options WHERE group_key = ? AND group_key != option_key AND deleted_at IS NULL ORDER BY display_order, option_value ASC");
$property_types_stmt->execute(['property_type']);
$property_types_for_filter = $property_types_stmt->fetchAll(PDO::FETCH_COLUMN);

// =================================================================
// 2. بناء الاستعلام الديناميكي (النسخة الموحدة)
// =================================================================
$sql_from_joins = "
    FROM properties p
    LEFT JOIN branches b ON p.branch_id = b.id
";
$sql_where = " WHERE p.deleted_at IS NULL ";
$params = [];

// تطبيق فلتر الفروع التلقائي أولاً
$sql_where .= build_branches_query_condition('p', $params);

// تطبيق بقية الفلاتر
if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (p.property_name LIKE ? OR p.property_code LIKE ? OR b.branch_name LIKE ?) ";
    array_push($params, $search_term, $search_term, $search_term);
}
if (!empty($filter_type)) { $sql_where .= " AND p.property_type = ? "; $params[] = $filter_type; }
if (!empty($filter_ownership)) { $sql_where .= " AND p.ownership_type = ? "; $params[] = $filter_ownership; }
if (!empty($filter_status)) { $sql_where .= " AND p.status = ? "; $params[] = $filter_status; }
if (!empty($filter_branch_id)) { $sql_where .= " AND p.branch_id = ? "; $params[] = $filter_branch_id; }

// =================================================================
// 3. حساب الإحصائيات والإجمالي
// =================================================================
// استعلام إحصائيات العقارات
$stats_sql = "SELECT COUNT(p.id) AS total_count, SUM(p.property_value) AS total_value, SUM(p.area) AS total_area {$sql_from_joins} {$sql_where}";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute($params);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// استعلام إحصائيات الوحدات
$units_count_sql = "SELECT COUNT(u.id) FROM units u JOIN properties p ON u.property_id = p.id LEFT JOIN branches b ON p.branch_id = b.id {$sql_where} AND u.deleted_at IS NULL";
$units_count_stmt = $pdo->prepare($units_count_sql);
$units_count_stmt->execute($params);
$stats['total_units_count'] = $units_count_stmt->fetchColumn();

// حساب إجمالي السجلات للترقيم
$total_records = $stats['total_count'] ?? 0;
$total_pages = ceil($total_records / $limit);

// =================================================================
// 4. جلب سجلات الصفحة الحالية
// =================================================================
$data_sql = "
    SELECT p.*, b.branch_code, (SELECT COUNT(*) FROM units u WHERE u.property_id = p.id AND u.deleted_at IS NULL) as units_count
    {$sql_from_joins}
    {$sql_where}
    ORDER BY p.id DESC 
    LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$properties = $data_stmt->fetchAll();

$status_colors = ['نشط' => 'success', 'ملغي' => 'danger', 'مؤرشف' => 'secondary'];
?>

<!-- ============================================================= -->
<!-- بداية عرض الواجهة (HTML) - النسخة الكاملة والصحيحة          -->
<!-- ============================================================= -->

<!-- 1. شريط العنوان والأزرار الرئيسية -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-building ms-2"></i>إدارة العقارات</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <button onclick="window.print();" class="btn btn-sm btn-outline-dark"><i class="fas fa-print ms-1"></i> طباعة</button>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=properties/add&view_only=true" data-bs-title="إضافة عقار جديد">
            <i class="fas fa-plus-circle ms-1"></i>إضافة عقار جديد
        </button>
    </div>
</div>

<!-- 2. نموذج الفرز والبحث -->
<div class="card bg-light mb-4">
    <div class="card-body">
        <form action="index.php" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="properties">
            <div class="col-md-2"><label for="q" class="form-label">بحث شامل</label><input type="search" class="form-control" name="q" id="q" placeholder="ابحث بالاسم، الكود، المالك..." value="<?php echo htmlspecialchars($filter_q ?? ''); ?>"></div>
            <div class="col-md-2"><label for="branch_id" class="form-label">الفرع</label><select name="branch_id" id="branch_id" class="form-select"><option value="">كل الفروع</option><?php foreach ($branches_for_filter as $branch): ?><option value="<?php echo $branch['id']; ?>" <?php echo ($filter_branch_id == $branch['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($branch['branch_name']); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label for="type" class="form-label">النوع</label><select name="type" id="type" class="form-select"><option value="">كل الأنواع</option><?php foreach ($property_types_for_filter as $type): ?><option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($filter_type == $type) ? 'selected' : ''; ?>><?php echo htmlspecialchars($type); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label for="ownership" class="form-label">التملك</label><select name="ownership" id="ownership" class="form-select"><option value="">الكل</option><option value="ملك" <?php echo ($filter_ownership == 'ملك') ? 'selected' : ''; ?>>ملك</option><option value="استثمار" <?php echo ($filter_ownership == 'استثمار') ? 'selected' : ''; ?>>استثمار</option></select></div>
            <div class="col-md-2"><label for="status" class="form-label">الحالة</label><select name="status" id="status" class="form-select"><option value="">كل الحالات</option><option value="نشط" <?php echo ($filter_status == 'نشط') ? 'selected' : ''; ?>>نشط</option><option value="ملغي" <?php echo ($filter_status == 'ملغي') ? 'selected' : ''; ?>>ملغي</option><option value="مؤرشف" <?php echo ($filter_status == 'مؤرشف') ? 'selected' : ''; ?>>مؤرشف</option></select></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search ms-1"></i> تطبيق</button><a href="index.php?page=properties" class="btn btn-secondary w-100 mt-1">إلغاء</a></div>
        </form>
    </div>
</div>

<!-- 3. شريط الإحصائيات -->
<div class="row mb-4 text-center">
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">إجمالي العقارات</h6><p class="card-text fs-4 fw-bold text-primary"><?php echo $stats['total_count'] ?? 0; ?></p></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">إجمالي الوحدات</h6><p class="card-text fs-4 fw-bold text-info"><?php echo $stats['total_units_count'] ?? 0; ?></p></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">إجمالي قيمة العقارات</h6><p class="card-text fs-4 fw-bold text-success"><?php echo number_format($stats['total_value'] ?? 0, 0); ?></p></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><h6 class="card-subtitle mb-2 text-muted">إجمالي المساحة</h6><p class="card-text fs-4 fw-bold"><?php echo number_format($stats['total_area'] ?? 0, 0); ?> <small>م²</small></p></div></div></div>
</div>

<!-- 4. جدول عرض البيانات (التصميم الأصلي الكامل + التحسينات) -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th style="width: 50px;">م</th>
                <th>#</th>
                <th>كود</th>
                <th>الفرع</th>
                <th>العقار</th>
                <th>النوع</th>
                <th>التملك</th>
                <th>المالك</th>
                <th>الصك</th>
                <th>القيمة</th>
                <th>المساحة</th>
                <th>الوحدات</th>
                <th>المدينة</th>
                <th>الحالة</th>
                <th>الملاحظات</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($properties)): ?>
                <tr><td colspan="15" class="text-center">لا توجد سجلات تطابق شروط البحث.</td></tr>
            <?php else: ?>
                <?php $row_counter = $offset + 1; ?>
                <?php foreach ($properties as $property): ?>
                    <tr>
                        <td><?php echo $row_counter++; ?></td>
                        <td><?php echo $property['id']; ?></td>
                        <td><?php echo htmlspecialchars($property['property_code'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($property['branch_code'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($property['property_name']); ?></td>
                        <td><?php echo htmlspecialchars($property['property_type']); ?></td>
                        <td><?php echo htmlspecialchars($property['ownership_type']); ?></td>
                        <td><?php echo htmlspecialchars($property['owner_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($property['deed_number'] ?? '—'); ?></td>
                        <td><?php echo number_format($property['property_value'] ?? 0, 2); ?></td>
                        <td><?php echo htmlspecialchars($property['area']); ?></td>
                        <td><span class="badge bg-dark"><?php echo $property['units_count']; ?></span></td>
                        <td><?php echo htmlspecialchars($property['city']); ?></td>
                        <td><span class="badge bg-<?php echo $status_colors[$property['status']] ?? 'dark'; ?>"><?php echo htmlspecialchars($property['status']); ?></span></td>
                        <td>
                            <?php if (!empty($property['notes'])): ?>
                                <i class="fas fa-info-circle text-primary" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($property['notes']); ?>"></i>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="print.php?template=property_profile_print&id=<?php echo $property['id']; ?>&view_mode=print" target="_blank" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="طباعة ملف العقار">
                                    <i class="fas fa-id-card"></i>
                                </a>
                                <span class="d-inline-block" data-bs-toggle="tooltip" title="تعديل">
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#mainModal" data-bs-url="index.php?page=properties/edit&id=<?php echo $property['id']; ?>&view_only=true" data-bs-title="تعديل العقار: <?php echo htmlspecialchars($property['property_name']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </span>
                                <a href="index.php?page=properties/delete&id=<?php echo $property['id']; ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="حذف" onclick="return confirm('سيتم نقل هذا العنصر إلى الأرشيف. هل أنت متأكد؟');"><i class="fas fa-trash-alt"></i></a>
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