<?php
// src/modules/documents/documents_view.php (الإصلاح النهائي والمضمون)

// --- 1. الإعدادات والفلترة ---
$records_per_page_options = [10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : 10;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

$filter_q = $_GET['q'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_status = $_GET['status'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;
$filter_owner_id = $_GET['owner_id'] ?? null;
$filter_property_id = $_GET['property_id'] ?? null;

// --- 2. بناء الاستعلام ---
$sql_select = "SELECT DISTINCT d.*, (SELECT COUNT(*) FROM entity_documents ed WHERE ed.document_id = d.id) as linked_entities_count";
$sql_count = "SELECT COUNT(DISTINCT d.id)";
$sql_from = " FROM documents d ";
$sql_joins = "";
$sql_where = " WHERE d.deleted_at IS NULL ";
$params = [];

if (!empty($filter_q)) { $sql_where .= " AND (d.document_name LIKE ? OR d.document_number LIKE ? OR d.notes LIKE ?) "; array_push($params, "%$filter_q%", "%$filter_q%", "%$filter_q%"); }
if (!empty($filter_type)) { $sql_where .= " AND d.document_type = ? "; $params[] = $filter_type; }
if (!empty($filter_status)) { $sql_where .= " AND d.status = ? "; $params[] = $filter_status; }

if (!empty($filter_branch_id)) {
    $sql_joins .= " LEFT JOIN entity_documents ed_link ON d.id = ed_link.document_id ";
    $sql_joins .= " LEFT JOIN properties p_link ON ed_link.entity_id = p_link.id AND ed_link.entity_type = 'property' ";
    $sql_joins .= " LEFT JOIN owner_branches ob_link ON ed_link.entity_id = ob_link.owner_id AND ed_link.entity_type = 'owner' ";
    $sql_where .= " AND ( (ed_link.entity_type = 'branch' AND ed_link.entity_id = ?) OR (p_link.branch_id = ?) OR (ob_link.branch_id = ?) ) ";
    array_push($params, $filter_branch_id, $filter_branch_id, $filter_branch_id);
}

if (!empty($filter_owner_id)) { 
    $sql_joins .= " LEFT JOIN entity_documents ed_owner_link ON d.id = ed_owner_link.document_id ";
    $sql_where .= " AND ed_owner_link.entity_type = 'owner' AND ed_owner_link.entity_id = ? ";
    $params[] = $filter_owner_id;
}
if (!empty($filter_property_id)) { 
    $sql_joins .= " LEFT JOIN entity_documents ed_prop_link ON d.id = ed_prop_link.document_id ";
    $sql_where .= " AND ed_prop_link.entity_type = 'property' AND ed_prop_link.entity_id = ? ";
    $params[] = $filter_property_id;
}

// --- 3. جلب الإحصائيات (لا تغيير) ---
$stats_sql = "SELECT (SELECT COUNT(*) FROM documents WHERE deleted_at IS NULL) as total, (SELECT COUNT(*) FROM documents WHERE deleted_at IS NULL AND status = 'active') as active, (SELECT COUNT(*) FROM documents WHERE deleted_at IS NULL AND expiry_date IS NOT NULL AND expiry_date < CURDATE()) as expired";
$stats = $pdo->query($stats_sql)->fetch(PDO::FETCH_ASSOC);

// --- 4. جلب البيانات الرئيسية ---
$count_stmt = $pdo->prepare($sql_count . $sql_from . $sql_joins . $sql_where);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

if ($total_records > 0) {
    $data_stmt = $pdo->prepare($sql_select . $sql_from . $sql_joins . $sql_where . " ORDER BY d.id DESC LIMIT {$limit} OFFSET {$offset}");
    $data_stmt->execute($params);
    $documents = $data_stmt->fetchAll();
} else {
    $documents = [];
}

// --- 5. جلب بيانات الفلاتر والقواميس ---
$types_map = $pdo->query("SELECT option_key, option_value FROM lookup_options WHERE group_key = 'documents_type' AND option_key != 'documents_type'")->fetchAll(PDO::FETCH_KEY_PAIR);
$status_map_stmt = $pdo->query("SELECT option_key, option_value, bg_color, color FROM lookup_options WHERE group_key = 'status' AND option_key != 'status'");
$status_map = [];
foreach($status_map_stmt->fetchAll(PDO::FETCH_ASSOC) as $status) { $status_map[$status['option_key']] = $status; }
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL")->fetchAll();
$owners_list = $pdo->query("SELECT id, owner_name FROM owners WHERE deleted_at IS NULL")->fetchAll();
$properties_list = $pdo->query("SELECT id, property_name FROM properties WHERE deleted_at IS NULL")->fetchAll();
?>

<!-- =============================================== -->
<!-- HTML: (لا تغيير هنا)                            -->
<!-- =============================================== -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">إدارة الوثائق (<?= $total_records ?>)</h2></div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <button onclick="window.print();" class="btn btn-outline-secondary"><i class="ti ti-printer me-2"></i>طباعة</button>
                    <a href="#" class="btn"><i class="ti ti-upload me-2"></i>إجراءات متعددة</a>
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=documents/add&view_only=true"><i class="ti ti-plus me-2"></i>إضافة وثيقة</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <!-- بطاقات الإحصائيات -->
        <div class="row row-cards mb-4">
            <div class="col-md-4"><div class="card bg-primary text-primary-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-primary"><i class="ti ti-file-text"></i></div></div><div class="card-body"><h3 class="card-title m-0">إجمالي الوثائق</h3><p class="h1 mt-1 mb-0"><?= $stats['total'] ?? 0 ?></p></div></div></div>
            <div class="col-md-4"><div class="card bg-success text-success-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-success"><i class="ti ti-file-check"></i></div></div><div class="card-body"><h3 class="card-title m-0">الوثائق النشطة</h3><p class="h1 mt-1 mb-0"><?= $stats['active'] ?? 0 ?></p></div></div></div>
            <div class="col-md-4"><div class="card bg-danger text-danger-fg"><div class="card-stamp"><div class="card-stamp-icon bg-white text-danger"><i class="ti ti-file-alert"></i></div></div><div class="card-body"><h3 class="card-title m-0">الوثائق المنتهية</h3><p class="h1 mt-1 mb-0"><?= $stats['expired'] ?? 0 ?></p></div></div></div>
        </div>

        <!-- بطاقة الفلترة المنفصلة -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="index.php" method="GET">
                    <input type="hidden" name="page" value="documents">
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">بحث شامل</label><input type="search" name="q" class="form-control" placeholder="ابحث بالاسم، الرقم، الملاحظات..." value="<?= htmlspecialchars($filter_q ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">الفرع</label><select name="branch_id" class="form-select select2-init"><option value="">كل الفروع</option><?php foreach($branches_list as $item):?><option value="<?=$item['id']?>" <?= ($filter_branch_id == $item['id'])?'selected':''?>><?=htmlspecialchars($item['branch_name'])?></option><?php endforeach;?></select></div>
                        <div class="col-md-3"><label class="form-label">المالك</label><select name="owner_id" class="form-select select2-init"><option value="">كل الملاك</option><?php foreach($owners_list as $item):?><option value="<?=$item['id']?>" <?= ($filter_owner_id == $item['id'])?'selected':''?>><?=htmlspecialchars($item['owner_name'])?></option><?php endforeach;?></select></div>
                        <div class="col-md-3"><label class="form-label">العقار</label><select name="property_id" class="form-select select2-init"><option value="">كل العقارات</option><?php foreach($properties_list as $item):?><option value="<?=$item['id']?>" <?= ($filter_property_id == $item['id'])?'selected':''?>><?=htmlspecialchars($item['property_name'])?></option><?php endforeach;?></select></div>
                        <div class="col-md-3"><label class="form-label">النوع</label><select name="type" class="form-select"><option value="">كل الأنواع</option><?php foreach($types_map as $key => $value):?><option value="<?= $key ?>" <?= ($filter_type == $key) ? 'selected' : '' ?>><?= $value ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">الحالة</label><select name="status" class="form-select"><option value="">كل الحالات</option><?php foreach($status_map as $key => $status): ?><option value="<?= $key ?>" <?= ($filter_status == $key) ? 'selected' : '' ?>><?= $status['option_value'] ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-2 d-flex align-items-end"><div class="btn-list"><button type="submit" class="btn btn-primary">تطبيق</button><a href="index.php?page=documents" class="btn"><i class="ti ti-refresh"></i></a></div></div>
                    </div>
                </form>
            </div>
        </div>

        <!-- بطاقة الجدول الرئيسية -->
        <div class="card">
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap table-hover">
                    <thead><tr><th class="w-1"><input class="form-check-input m-0 align-middle" type="checkbox" onchange="toggleAllCheckboxes(this)"></th><th>م</th><th>اسم ونوع الوثيقة</th><th>رقم الوثيقة</th><th>الحالة</th><th>تاريخ الانتهاء</th><th>الكيانات المرتبطة</th><th>ملاحظات</th><th class="w-1"></th></tr></thead>
                    <tbody>
                        <?php if (empty($documents)): ?>
                            <tr><td colspan="9" class="text-center p-4">لا توجد نتائج تطابق بحثك.</td></tr>
                        <?php else: $row_counter = $offset + 1; foreach($documents as $doc): ?>
                        <tr>
                            <td><input class="form-check-input m-0 align-middle" type="checkbox" name="row_id[]" value="<?= $doc['id'] ?>"></td>
                            <td><span class="text-muted"><?= $row_counter++ ?></span></td>
                            <td>
                                <div><?= htmlspecialchars($doc['document_name']) ?></div>
                                <div class="text-muted"><?= htmlspecialchars($types_map[$doc['document_type']] ?? $doc['document_type']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($doc['document_number']) ?></td>
                            <td>
                                <?php $status_key=$doc['status']; $status_name=$status_map[$status_key]['option_value']??$status_key; $bg_color=!empty($status_map[$status_key]['bg_color'])?$status_map[$status_key]['bg_color']:'#6c757d'; $color=!empty($status_map[$status_key]['color'])?$status_map[$status_key]['color']:'#ffffff'; ?>
                                <span class="badge" style="background-color:<?=$bg_color?>; color:<?=$color?>;"><?=htmlspecialchars($status_name)?></span>
                            </td>
                            <td><?= htmlspecialchars($doc['expiry_date']) ?></td>
                            <td><span class="badge bg-blue-lt"><?= $doc['linked_entities_count'] ?></span></td>
                            <td><?php if (!empty($doc['notes'])):?><i class="ti ti-info-circle text-primary" data-bs-toggle="tooltip" title="<?=htmlspecialchars($doc['notes'])?>"></i><?php endif;?></td>
                            <td class="text-end"><div class="btn-list flex-nowrap"><a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=documents/edit&id=<?=$doc['id']?>&view_only=true">تعديل</a><a href="index.php?page=documents/delete&id=<?=$doc['id']?>" class="btn btn-outline-danger btn-icon confirm-delete" title="حذف الوثيقة"><i class="ti ti-trash"></i></a></div></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center">
                <p class="m-0 text-muted">عرض <span><?= count($documents) ?></span> من <span><?= $total_records ?></span> سجل</p>
                <div class="m-auto"><?php render_smart_pagination($current_page, $total_pages, $_GET); ?></div>
            </div>
        </div>
    </div>
</div>