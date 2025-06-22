<?php
// src/modules/owners/owners_view.php

// 1. الإعدادات والفلترة
$limit = $_GET['limit'] ?? 10;
$current_page = $_GET['p'] ?? 1;
$offset = ($current_page - 1) * $limit;
$filter_q = $_GET['q'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;

// 2. بناء الاستعلام
$sql_where = " WHERE o.deleted_at IS NULL ";
$params = [];

// --- فلترة بناءً على صلاحيات الفروع (الأهم) ---
$user_branch_ids = $_SESSION['user_branch_ids'] ?? [];
if ($user_branch_ids !== 'ALL') {
    if (empty($user_branch_ids)) {
        $sql_where .= " AND 1=0 "; // لا تعرض شيئًا إذا لم يكن للمستخدم فروع
    } else {
        $placeholders = implode(',', array_fill(0, count($user_branch_ids), '?'));
        // هذا الاستعلام الفرعي يتحقق مما إذا كان المالك مرتبطًا بأي من الفروع المسموح بها للمستخدم
        $sql_where .= " AND EXISTS (SELECT 1 FROM owner_branches ob WHERE ob.owner_id = o.id AND ob.branch_id IN ($placeholders)) ";
        foreach ($user_branch_ids as $branch_id) { $params[] = $branch_id; }
    }
}

// --- بقية الفلاتر ---
if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (o.owner_name LIKE ? OR o.owner_code LIKE ? OR o.id_number LIKE ? OR o.mobile LIKE ?) ";
    array_push($params, $search_term, $search_term, $search_term, $search_term);
}
if (!empty($filter_branch_id)) {
    $sql_where .= " AND o.id IN (SELECT owner_id FROM owner_branches WHERE branch_id = ?) ";
    $params[] = $filter_branch_id;
}


// 3. جلب البيانات والإحصائيات
$count_params = $params;
$total_records_stmt = $pdo->prepare("SELECT COUNT(DISTINCT o.id) FROM owners o {$sql_where}");
$total_records_stmt->execute($count_params);
$total_records = $total_records_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

$data_sql = "
    SELECT o.*, GROUP_CONCAT(b.branch_code SEPARATOR ', ') as branch_codes
    FROM owners o
    LEFT JOIN owner_branches ob ON o.id = ob.owner_id
    LEFT JOIN branches b ON ob.branch_id = b.id
    {$sql_where}
    GROUP BY o.id
    ORDER BY o.id DESC
    LIMIT {$limit} OFFSET {$offset}
";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$owners = $data_stmt->fetchAll();

$branches_for_filter = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL")->fetchAll();
?>

<!-- واجهة عرض الملاك -->
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">إدارة الملاك (<?= $total_records ?>)</h3>
        <div class="btn-list">
             <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=owners/add&view_only=true">
                <i class="ti ti-plus me-2"></i>إضافة مالك جديد
            </a>
        </div>
    </div>
    <div class="card-body border-bottom py-3">
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="owners">
            <div class="row g-2">
                <div class="col"><input type="search" name="q" class="form-control" placeholder="ابحث عن اسم، كود، هوية..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
                <div class="col"><select class="form-select select2-init" name="branch_id"><option value="">كل الفروع</option><?php foreach($branches_for_filter as $branch):?><option value="<?=$branch['id']?>" <?= ($filter_branch_id == $branch['id']) ? 'selected' : '' ?>><?=htmlspecialchars($branch['branch_name'])?></option><?php endforeach;?></select></div>
                <div class="col-auto"><button type="submit" class="btn btn-primary">بحث</button></div>
                <div class="col-auto"><a href="index.php?page=owners" class="btn btn-secondary" title="إعادة تعيين"><i class="ti ti-refresh"></i></a></div>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap table-hover">
            <thead>
                <tr>
                    <th>الاسم / الكود</th>
                    <th>النوع</th>
                    <th>الفروع المرتبطة</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($owners)): ?>
                    <tr><td colspan="5" class="text-center">لا توجد نتائج.</td></tr>
                <?php else: foreach ($owners as $owner): ?>
                    <tr>
                        <td>
                            <div><?= htmlspecialchars($owner['owner_name']) ?></div>
                            <div class="text-muted"><?= htmlspecialchars($owner['owner_code'] ?? 'N/A') ?></div>
                        </td>
                        <td><?= htmlspecialchars($owner['owner_type']) ?></td>
                        <td><span class="badge bg-secondary-lt"><?= htmlspecialchars($owner['branch_codes'] ?? 'غير مرتبط') ?></span></td>
                        <td><span class="badge bg-<?= ($owner['status'] === 'نشط') ? 'success' : 'danger' ?>-lt"><?= htmlspecialchars($owner['status']) ?></span></td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=owners/edit&id=<?= $owner['id'] ?>&view_only=true">تعديل</a>
                                <a href="#" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=owners/branches_modal&id=<?= $owner['id'] ?>&view_only=true">الفروع</a>
                                <!-- زر الحذف سيضاف لاحقاً -->
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-muted">عرض <span><?= count($owners) ?></span> من <span><?= $total_records ?></span> سجل</p>
        <?php render_smart_pagination($current_page, $total_pages, ['page' => 'owners', 'q' => $filter_q, 'branch_id' => $filter_branch_id]); ?>
    </div>
</div>