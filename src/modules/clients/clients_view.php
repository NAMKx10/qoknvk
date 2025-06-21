<?php
// PHP - جلب البيانات والفلترة
// 1. الإعدادات
$limit = 10;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

// 2. بناء الاستعلام
$sql_where = " WHERE c.deleted_at IS NULL ";
$params = [];
// تطبيق فلتر الفروع التلقائي (سيتم تفعيله لاحقًا)
// $sql_where .= build_branches_query_condition('p', $params);

// 3. جلب البيانات
$data_sql = "
    SELECT c.*,
           (SELECT COUNT(*) FROM contracts_rental cr WHERE cr.client_id = c.id AND cr.deleted_at IS NULL) as contracts_count,
           (SELECT COUNT(*) FROM client_branches cb WHERE cb.client_id = c.id) as branch_count
    FROM clients c
    {$sql_where}
    ORDER BY c.id DESC
    LIMIT {$limit} OFFSET {$offset}
";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$clients = $data_stmt->fetchAll();

// 4. حساب الإجمالي
$total_records_stmt = $pdo->prepare("SELECT COUNT(*) FROM clients c {$sql_where}");
$total_records_stmt->execute($params);
$total_records = $total_records_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);
?>

<!-- HTML - واجهة Tabler -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">إدارة العملاء</h2>
                <div class="text-muted mt-1"><?= $total_records ?> عميل</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=clients/add&view_only=true" data-bs-title="إضافة عميل جديد">
                    <i class="ti ti-plus me-2"></i>إضافة عميل
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap">
            <thead>
                <tr>
                    <th>العميل</th>
                    <th>البيانات الرئيسية</th>
                    <th>الفروع</th>
                    <th>العقود</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($clients)): ?>
                    <tr><td colspan="6" class="text-center">لا توجد بيانات.</td></tr>
                <?php else: foreach($clients as $client): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="avatar me-2"><?= mb_substr($client['client_name'], 0, 2) ?></span>
                            <div class="flex-fill">
                                <div class="font-weight-medium"><?= htmlspecialchars($client['client_name']) ?></div>
                                <div class="text-muted"><?= htmlspecialchars($client['client_type'] ?? '—') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>هوية/سجل: <?= htmlspecialchars($client['id_number'] ?? '—') ?></div>
                        <div class="text-muted">جوال: <?= htmlspecialchars($client['mobile'] ?? '—') ?></div>
                    </td>
                    <td>
                        <?php if ($client['branch_count'] > 0): ?>
                            <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=clients/branches_modal&id=<?= $client['id'] ?>&view_only=true" data-bs-title="الفروع المرتبطة">
                                <?= $client['branch_count'] ?> فرع/فروع
                            </button>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $client['contracts_count'] ?></td>
                    <td><span class="badge bg-<?= ($client['status'] === 'نشط') ? 'success' : 'danger' ?>-lt"><?= htmlspecialchars($client['status']) ?></span></td>
                    <td class="text-end">
                        <a href="#" class="btn btn-sm">تعديل</a>
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
                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>"><a class="page-link" href="?page=clients&p=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>