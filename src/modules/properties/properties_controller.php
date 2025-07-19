<?php
// src/modules/properties/properties_controller.php (النسخة النهائية الصحيحة)

global $pdo;

// 1. الإعدادات والفلترة
$records_per_page_options = [10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : 10;
$current_page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

$filter_q = $_GET['q'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_ownership = $_GET['ownership'] ?? null;
$filter_status = $_GET['status'] ?? null;

// 2. بناء جملة الاستعلام
$sql_where = " WHERE p.deleted_at IS NULL ";
$params = [];
$sql_where .= build_branches_query_condition('p', $params);

if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (p.property_name LIKE ? OR p.property_code LIKE ?)";
    array_push($params, $search_term, $search_term);
}
if (!empty($filter_branch_id)) { $sql_where .= " AND p.branch_id = ? "; $params[] = $filter_branch_id; }
if (!empty($filter_type)) { $sql_where .= " AND p.property_type = ? "; $params[] = $filter_type; }
if (!empty($filter_ownership)) { $sql_where .= " AND p.ownership_type = ? "; $params[] = $filter_ownership; }
if (!empty($filter_status)) { $sql_where .= " AND p.status = ? "; $params[] = $filter_status; }

// 3. جلب الإحصائيات
$stats_params = $params;
$stats_sql = "SELECT COUNT(p.id) AS total_properties, SUM(p.property_value) as total_value, SUM(p.area) as total_area FROM properties p {$sql_where}";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute($stats_params);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

$units_sql = "SELECT COUNT(u.id) FROM units u JOIN properties p ON u.property_id = p.id {$sql_where} AND u.deleted_at IS NULL";
$units_stmt = $pdo->prepare($units_sql);
$units_stmt->execute($params);
$stats['total_units'] = $units_stmt->fetchColumn();

$total_records = $stats['total_properties'] ?? 0;
$total_pages = ceil($total_records / $limit);

// 4. جلب البيانات الرئيسية مع كل الأعمدة المطلوبة
$data_sql = "
    SELECT 
        p.*, 
        b.branch_code,
        (SELECT COUNT(id) FROM units u WHERE u.property_id = p.id AND u.deleted_at IS NULL) as units_count,
        (SELECT COUNT(id) FROM property_owners po WHERE po.property_id = p.id) as owners_count,
        (SELECT COUNT(id) FROM entity_documents ed WHERE ed.entity_id = p.id AND ed.entity_type = 'property') as documents_count,
        (SELECT COUNT(DISTINCT cu.contract_id) FROM contract_units cu JOIN units u ON cu.unit_id = u.id WHERE u.property_id = p.id) as rental_contracts_count,
        (SELECT COUNT(id) FROM contracts_supply cs WHERE cs.property_id = p.id AND cs.deleted_at IS NULL) as supply_contracts_count,
        (SELECT COUNT(DISTINCT client_id) FROM contracts_rental cr JOIN contract_units cu ON cr.id = cu.contract_id JOIN units u ON cu.unit_id = u.id WHERE u.property_id = p.id) as clients_count,
        (SELECT COUNT(DISTINCT supplier_id) FROM contracts_supply cs WHERE cs.property_id = p.id) as suppliers_count
    FROM properties p 
    LEFT JOIN branches b ON p.branch_id = b.id 
    {$sql_where} 
    ORDER BY p.id DESC 
    LIMIT {$limit} OFFSET {$offset}
";

$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$properties = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. جلب بيانات الفلاتر
$branches_for_filter = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL")->fetchAll();
$property_types_for_filter = get_lookup_options($pdo, 'property_type');
$ownership_types_for_filter = get_lookup_options($pdo, 'ownership_type');
$statuses_for_filter = get_lookup_options($pdo, 'status', true);

$statuses_map_stmt = $pdo->query("SELECT option_key, option_value, bg_color FROM lookup_options WHERE group_key = 'status' AND option_key != 'status'");
$statuses_map = [];
foreach ($statuses_map_stmt as $row) {
    $statuses_map[$row['option_key']] = ['name' => $row['option_value'], 'bg_color' => $row['bg_color']];
}

// 6. استدعاء الواجهة
require_once __DIR__ . '/properties_view.php';
?>