<?php
// src/modules/branches/branches_controller.php (النسخة الكاملة والصحيحة)

global $pdo;

// 1. الإعدادات والفلترة
$records_per_page_options = [10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : 10;
$current_page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

$filter_q = $_GET['q'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_status = $_GET['status'] ?? null;

// 2. بناء الاستعلام
$sql_where = " WHERE b.deleted_at IS NULL ";
$params = [];
if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (b.branch_name LIKE ? OR b.branch_code LIKE ? OR b.registration_number LIKE ?)";
    array_push($params, $search_term, $search_term, $search_term);
}
if (!empty($filter_type)) { $sql_where .= " AND b.branch_type = ? "; $params[] = $filter_type; }
if (!empty($filter_status)) { $sql_where .= " AND b.status = ? "; $params[] = $filter_status; }

// 3. جلب الإحصائيات
$stats_query = "SELECT COUNT(id) as total,
    SUM(CASE WHEN status='Active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN branch_type='منشأة' THEN 1 ELSE 0 END) as companies,
    SUM(CASE WHEN branch_type='فرد' THEN 1 ELSE 0 END) as individuals
FROM branches WHERE deleted_at IS NULL";
$stats = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC);

// 4. جلب البيانات الرئيسية
$data_sql = "
    SELECT b.*,
        (SELECT COUNT(id) FROM properties WHERE branch_id = b.id AND deleted_at IS NULL) as properties_count,
        (SELECT COUNT(u.id) FROM units u JOIN properties p ON u.property_id = p.id WHERE p.branch_id = b.id AND u.deleted_at IS NULL) as units_count
    FROM branches b {$sql_where} ORDER BY b.id DESC LIMIT {$limit} OFFSET {$offset}";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$branches = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

$total_records_stmt = $pdo->prepare("SELECT COUNT(*) FROM branches b {$sql_where}");
$total_records_stmt->execute($params);
$total_records = $total_records_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// 5. بيانات الفلاتر
$branch_types_filter = $pdo->query("SELECT option_value FROM lookup_options WHERE group_key = 'entity_type' AND option_key != 'entity_type' AND deleted_at IS NULL")->fetchAll(PDO::FETCH_COLUMN);
$statuses_map_stmt = $pdo->query("SELECT option_key, option_value, bg_color FROM lookup_options WHERE group_key = 'status'");
$statuses_map = [];
foreach ($statuses_map_stmt as $row) { $statuses_map[$row['option_key']] = ['name' => $row['option_value'], 'bg_color' => $row['bg_color']]; }
$statuses_filter = $statuses_map;


// 6. استدعاء الواجهة
require_once __DIR__ . '/branches_view.php';
?>