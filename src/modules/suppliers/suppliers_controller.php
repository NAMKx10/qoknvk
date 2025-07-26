<?php
// src/modules/suppliers/suppliers_controller.php

global $pdo;

// --- 1. الإعدادات والفلترة ---
$records_per_page_options = [10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : 10;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

$filter_q = $_GET['q'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_status = $_GET['status'] ?? null;

// --- 2. بناء الاستعلام وتطبيق الأمان ---
$sql_where = " WHERE s.deleted_at IS NULL ";
$params = [];

if ($_SESSION['user_branch_ids'] !== 'ALL' && !empty($_SESSION['user_branch_ids'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['user_branch_ids']), '?'));
    $sql_where .= " AND EXISTS (SELECT 1 FROM supplier_branches sb WHERE sb.supplier_id = s.id AND sb.branch_id IN ($placeholders)) ";
    foreach ($_SESSION['user_branch_ids'] as $branch_id) { $params[] = $branch_id; }
}

if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (s.supplier_name LIKE ? OR s.supplier_code LIKE ? OR s.registration_number LIKE ?) ";
    array_push($params, $search_term, $search_term, $search_term);
}
if (!empty($filter_branch_id)) {
    $sql_where .= " AND EXISTS (SELECT 1 FROM supplier_branches sb WHERE sb.supplier_id = s.id AND sb.branch_id = ?) ";
    $params[] = $filter_branch_id;
}
if (!empty($filter_type)) { $sql_where .= " AND s.supplier_type = ? "; $params[] = $filter_type; }
if (!empty($filter_status)) { $sql_where .= " AND s.status = ? "; $params[] = $filter_status; }


// --- 3. جلب الإحصائيات ---
$stats_params = $params;
$stats_sql = "SELECT 
    COUNT(s.id) as total_suppliers,
    SUM(CASE WHEN s.status = 'Active' THEN 1 ELSE 0 END) as active_suppliers,
    SUM(CASE WHEN s.supplier_type = 'منشأة' THEN 1 ELSE 0 END) as companies,
    SUM(CASE WHEN s.supplier_type = 'فرد' THEN 1 ELSE 0 END) as individuals
FROM suppliers s {$sql_where}";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute($stats_params);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);


// --- 4. جلب البيانات الرئيسية مع ترقيم الصفحات ---
$total_records = $stats['total_suppliers'] ?? 0;
$total_pages = ceil($total_records / $limit);

$data_sql = "
    SELECT s.*,
           (SELECT COUNT(*) FROM contracts_supply cs WHERE cs.supplier_id = s.id AND cs.deleted_at IS NULL) as contracts_count,
           (SELECT COUNT(*) FROM supplier_branches sb WHERE sb.supplier_id = s.id) as branch_count,
           lo.option_value as status_name,
           lo.bg_color as status_color
    FROM suppliers s
    LEFT JOIN lookup_options lo ON s.status = lo.option_key AND lo.group_key = 'status'
    {$sql_where}
    ORDER BY s.id DESC
    LIMIT {$limit} OFFSET {$offset}
";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$suppliers = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 5. جلب بيانات الفلاتر الديناميكية ---
$branches_for_filter = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL ORDER BY branch_name ASC")->fetchAll();
$supplier_types_for_filter = get_lookup_options($pdo, 'entity_type');
$statuses_for_filter = get_lookup_options($pdo, 'status', true);

$statuses_map_stmt = $pdo->query("SELECT option_key, option_value, bg_color FROM lookup_options WHERE group_key = 'status'");
$statuses_map = [];
foreach ($statuses_map_stmt as $row) {
    $statuses_map[$row['option_key']] = ['name' => $row['option_value'], 'bg_color' => $row['bg_color']];
}

// --- 6. استدعاء الواجهة ---
require_once __DIR__ . '/suppliers_view.php';
?>