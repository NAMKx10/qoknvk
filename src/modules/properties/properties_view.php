<?php
// =================================================================
// PHP - Final Blueprint Version
// =================================================================
// 1. Settings & Filters
$records_per_page_options = [10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : 10; 
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

$filter_q = $_GET['q'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_ownership = $_GET['ownership'] ?? null;
$filter_status = $_GET['status'] ?? null;

// 2. Build Query
$sql_where = " WHERE p.deleted_at IS NULL ";
$params = [];
$sql_where .= build_branches_query_condition('p', $params);

if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (p.property_name LIKE ? OR p.property_code LIKE ? OR p.owner_name LIKE ? OR p.deed_number LIKE ?) ";
    array_push($params, $search_term, $search_term, $search_term, $search_term);
}
if (!empty($filter_branch_id)) { $sql_where .= " AND p.branch_id = ? "; $params[] = $filter_branch_id; }
if (!empty($filter_type)) { $sql_where .= " AND p.property_type = ? "; $params[] = $filter_type; }
if (!empty($filter_ownership)) { $sql_where .= " AND p.ownership_type = ? "; $params[] = $filter_ownership; }
if (!empty($filter_status)) { $sql_where .= " AND p.status = ? "; $params[] = $filter_status; }

// 3. Fetch Stats & Data
// --- أولاً: نحسب إحصائيات العقارات ---
$stats_params = $params; // نستخدم نسخة من المتغيرات للإحصائيات
$stats_sql = "SELECT COUNT(p.id) AS total_properties, SUM(p.property_value) as total_value, SUM(p.area) as total_area FROM properties p LEFT JOIN branches b ON p.branch_id = b.id {$sql_where}";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute($stats_params);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// --- ثانيًا: نحسب إحصائيات الوحدات بشكل منفصل ودقيق ---
// هذا الاستعلام الجديد يجلب عدد الوحدات فقط للعقارات التي تطابق الفلترة
$units_sql = "SELECT COUNT(u.id) FROM units u JOIN properties p ON u.property_id = p.id {$sql_where} AND u.deleted_at IS NULL";
$units_stmt = $pdo->prepare($units_sql);
$units_stmt->execute($params); // نستخدم المتغيرات الأصلية هنا
$stats['total_units'] = $units_stmt->fetchColumn();

$total_records = $stats['total_properties'] ?? 0;
$total_pages = ceil($total_records / $limit);

$data_sql = "SELECT p.*, b.branch_code, (SELECT COUNT(*) FROM units u WHERE u.property_id = p.id AND u.deleted_at IS NULL) as units_count FROM properties p LEFT JOIN branches b ON p.branch_id = b.id {$sql_where} ORDER BY p.id DESC LIMIT {$limit} OFFSET {$offset}";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$properties = $data_stmt->fetchAll();

// Data for Filters
$branches_for_filter = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL")->fetchAll();
$property_types_for_filter = $pdo->query("SELECT DISTINCT property_type FROM properties WHERE deleted_at IS NULL AND property_type IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- HTML - v6.0 (Layout Fixes) -->
<!-- HTML - Final Blueprint Version with Layout Fixes -->

<!-- 1. البطاقة العلوية الرئيسية (تحتوي على كل شيء) -->
<div class="card mb-4">
    <div class="card-body">
        <!-- 1a. صف العنوان والأزرار -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-0">إدارة العقارات</h2>
            </div>
            <div class="btn-list">
                <a href="#" class="btn btn-outline-secondary"><i class="ti ti-printer me-2"></i>طباعة</a>
                <a href="#" class="btn"><i class="ti ti-table-plus me-2"></i>إدخال متعدد</a>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=properties/add&view_only=true" data-bs-title="إضافة عقار جديد"><i class="ti ti-plus me-2"></i>إضافة عقار</a>
            </div>
        </div>

        <!-- 1b. صف بطاقات الإحصائيات -->
        <!-- Stat Cards with Background and Icons (Compact Version) -->
<div class="row row-cards mb-4">
    
    <!-- البطاقة الأولى -->
    <div class="col-md-6 col-lg-3">
        <div class="card bg-primary text-primary-fg">
            <div class="card-stamp"><div class="card-stamp-icon bg-white text-primary"><i class="ti ti-building-arch"></i></div></div>
            <div class="card-body">
                <h3 class="card-title m-0">إجمالي العقارات</h3>
                <p class="h1 mt-1 mb-0"><?= $total_records ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- البطاقة الثانية -->
    <div class="col-md-6 col-lg-3">
        <div class="card bg-green text-green-fg">
            <div class="card-stamp"><div class="card-stamp-icon bg-white text-green"><i class="ti ti-door"></i></div></div>
            <div class="card-body">
                <h3 class="card-title m-0">إجمالي الوحدات</h3>
                <p class="h1 mt-1 mb-0"><?= $stats['total_units'] ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- البطاقة الثالثة -->
    <div class="col-md-6 col-lg-3">
        <div class="card bg-warning text-warning-fg">
            <div class="card-stamp"><div class="card-stamp-icon bg-white text-warning"><i class="ti ti-currency-real"></i></div></div>
            <div class="card-body">
                <h3 class="card-title m-0">إجمالي قيمة العقارات</h3>
                <p class="h1 mt-1 mb-0"><?= number_format($stats['total_value'] ?? 0, 0) ?></p>
            </div>
        </div>
    </div>

    <!-- البطاقة الرابعة -->
    <div class="col-md-6 col-lg-3">
        <div class="card bg-azure text-azure-fg">
            <div class="card-stamp"><div class="card-stamp-icon bg-white text-azure"><i class="ti ti-ruler-measure"></i></div></div>
            <div class="card-body">
                <h3 class="card-title m-0">إجمالي المساحة</h3>
                <p class="h1 mt-1 mb-0"><?= number_format($stats['total_area'] ?? 0, 2) ?> <small>م²</small></p>
            </div>
        </div>
    </div>

</div>

        <!-- 1c. صف الفلترة والبحث -->
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="properties">
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">بحث شامل</label><input type="search" name="q" class="form-control" placeholder="ابحث..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
                <div class="col-md-2"><label class="form-label">الفرع</label><select class="form-select select2-init" name="branch_id"><option value="">الكل</option><?php foreach($branches_for_filter as $branch):?><option value="<?=$branch['id']?>" <?= ($filter_branch_id == $branch['id']) ? 'selected' : '' ?>><?=htmlspecialchars($branch['branch_name'])?></option><?php endforeach;?></select></div>
                <div class="col-md-2"><label class="form-label">النوع</label><select class="form-select select2-init" name="type"><option value="">الكل</option><?php foreach($property_types_for_filter as $type):?><option value="<?=$type?>" <?= ($filter_type == $type) ? 'selected' : '' ?>><?=htmlspecialchars($type)?></option><?php endforeach;?></select></div>
                <div class="col-md-2"><label class="form-label">التملك</label><select class="form-select" name="ownership"><option value="">الكل</option><option value="ملك">ملك</option><option value="استثمار">استثمار</option></select></div>
                <div class="col-md-1"><label class="form-label">الحالة</label><select class="form-select" name="status"><option value="">الكل</option><option value="نشط">نشط</option><option value="ملغي">ملغي</option></select></div>
                <div class="col-md-2">
                <label class="form-label"> </label> <!-- label فارغة للمحاذاة -->
                <div class="btn-list">
                    <button type="submit" class="btn btn-primary">تطبيق</button>
                    <a href="index.php?page=properties" class="btn" title="إعادة تعيين الفرز">
                    <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </div>
            </div>
        </form>
    </div>
</div>
<!-- Main Data Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap">
            <thead>
    <tr>
        <th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" onchange="toggleAllCheckboxes(this)"></th>
        <th>م</th>
        <th>صورة</th>
        <th>العقار</th> <!-- تم تغيير الاسم -->
        <th>المالك / الصك</th> <!-- تم دمج عمودين -->
        <th>قيمة العقار</th>
        <th>الوحدات</th>
        <th>الحالة</th>
        <th>ملاحظات</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
    <?php if(empty($properties)): ?><tr><td colspan="10" class="text-center">لا توجد نتائج.</td></tr><?php else: $row_counter = $offset + 1; foreach($properties as $property): ?>
    <tr>
        <td><input class="form-check-input m-0 align-middle" type="checkbox" name="row_id[]" value="<?= $property['id'] ?>"></td>
        <td><span class="text-muted"><?= $row_counter++ ?></span></td>
        <td><span class="avatar" style="background-image: url(./assets/static/properties/default.jpg)"></span></td>
        
        <!-- === بداية عمود العقار المدمج === -->
        <td>
            <div class="fw-bold"><?= htmlspecialchars($property['property_name']) ?></div>
            <div class="text-muted" style="font-size: 0.9em;">
                <?= htmlspecialchars($property['branch_code'] ?? 'بدون فرع') ?> •
                <?= htmlspecialchars($property['property_type'] ?? 'بدون نوع') ?> •
                <?= htmlspecialchars($property['ownership_type'] ?? '') ?> •
                <?= number_format($property['area'] ?? 0, 0) ?> م² •
                <?= htmlspecialchars($property['city'] ?? '') ?>
            </div>
        </td>
        <!-- === نهاية عمود العقار المدمج === -->

        <!-- === بداية عمود المالك المدمج === -->
        <td>
            <div><?= htmlspecialchars($property['owner_name'] ?? '—') ?></div>
            <div class="text-muted" style="font-size: 0.9em;">صك: <?= htmlspecialchars($property['deed_number'] ?? '—') ?></div>
            <td><?= number_format($property['property_value'] ?? 0, 2) ?></td>
        </td>
        <!-- === نهاية عمود المالك المدمج === -->

        <td><div class="text-center"><?= $property['units_count'] ?></div></td>
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
        <div class="text-muted">
            عرض:
            <div class="mx-2 d-inline-block">
                <form action="index.php" method="GET" class="d-inline-block">
                    <input type="hidden" name="page" value="properties">
                    <input type="hidden" name="q" value="<?= htmlspecialchars($filter_q ?? '') ?>">
                    <input type="hidden" name="branch_id" value="<?= htmlspecialchars($filter_branch_id ?? '') ?>">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($filter_type ?? '') ?>">
                    <input type="hidden" name="ownership" value="<?= htmlspecialchars($filter_ownership ?? '') ?>">
                    <select name="limit" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach($records_per_page_options as $option): ?><option value="<?=$option?>" <?= ($limit == $option) ? 'selected' : '' ?>><?=$option?></option><?php endforeach; ?>
                    </select>
                </form>
            </div>
            سجلات
        </div>
        <ul class="pagination m-0 ms-auto">
            <!-- Pagination links will be generated by PHP -->
        </ul>
    </div>
</div>