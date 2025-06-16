<?php
$filter_q = $_GET['q'] ?? null;
$filter_status = $_GET['status'] ?? null;

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

// حساب الإجمالي
$total_records_stmt = $pdo->prepare("SELECT COUNT(*) FROM branches {$sql_where}");
$total_records_stmt->execute($params);
$total_records = $total_records_stmt->fetchColumn();

// إعدادات الترقيم
$limit = 10;
$total_pages = ceil($total_records / $limit);
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

// جلب البيانات
$data_sql = "SELECT * FROM branches {$sql_where} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$branches = $data_stmt->fetchAll();
?>

<!-- ============================================================= -->
<!-- HTML (الواجهة الأمامية) - هنا يكمن سحر Tabler                -->
<!-- ============================================================= -->

<!-- 1. عنوان الصفحة مع أزرار الإجراءات -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">إدارة الفروع</h2>
                <div class="text-muted mt-1"><?= $total_records ?> فرع</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" class="btn btn-primary" 
   data-bs-toggle="modal" 
   data-bs-target="#modal-add-branch"
   data-bs-url="index.php?page=branches/add&view_only=true">
    <i class="ti ti-plus me-2"></i>إضافة فرع جديد
</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. بطاقات الإحصائيات (مثال) -->
<div class="row row-deck row-cards mb-4">
    <!-- يمكنك إضافة إحصائيات هنا بنفس طريقة لوحة التحكم -->
</div>

<!-- 3. بطاقة الفلترة والجدول -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة الفروع</h3>
    </div>
    <div class="card-body border-bottom py-3">
        <div class="d-flex">
            <div class="text-muted">
                عرض:
                <div class="mx-2 d-inline-block">
                    <input type="text" class="form-control form-control-sm" value="8" size="3" aria-label="Invoices count">
                </div>
                مدخلات
            </div>
            <div class="ms-auto text-muted">
                بحث:
                <div class="ms-2 d-inline-block">
                    <input type="text" class="form-control form-control-sm" aria-label="Search invoice">
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap datatable">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>البيانات</th>
                    <th>الحالة</th>
                    <th>تاريخ الإنشاء</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($branches as $branch): ?>
                <tr>
                    <td>
                        <div class="d-flex py-1 align-items-center">
                            <span class="avatar me-2" style="background-image: url(./assets/static/avatars/default.svg)"></span>
                            <div class="flex-fill">
                                <div class="font-weight-medium"><?= htmlspecialchars($branch['branch_name']) ?></div>
                                <div class="text-muted"><?= htmlspecialchars($branch['branch_code'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($branch['registration_number'] ?? 'لا يوجد سجل تجاري') ?></div>
                        <div class="text-muted"><?= htmlspecialchars($branch['phone'] ?? 'لا يوجد هاتف') ?></div>
                    </td>
                    <td>
                        <?php if($branch['status'] === 'نشط'): ?>
                            <span class="badge bg-success-lt">نشط</span>
                        <?php else: ?>
                            <span class="badge bg-danger-lt">ملغي</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= date('Y-m-d', strtotime($branch['created_at'])) ?>
                    </td>
                    <td class="text-end">
                        <a href="#" class="btn btn-sm">تعديل</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- ترقيم الصفحات -->
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-muted">عرض <span>1</span> إلى <span>8</span> من <span><?= $total_records ?></span> مدخلات</p>
        <ul class="pagination m-0 ms-auto">
            <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1"><i class="ti ti-chevron-right"></i></a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#"><i class="ti ti-chevron-left"></i></a></li>
        </ul>
    </div>
</div>

<!-- نافذة منبثقة للإضافة (Modal) -->
<div class="modal modal-blur fade" id="modal-add-branch" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة فرع جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- هنا سنقوم بتحميل محتوى add_view.php باستخدام AJAX لاحقًا -->
                محتوى نموذج الإضافة سيأتي هنا...
            </div>
        </div>
    </div>
</div>