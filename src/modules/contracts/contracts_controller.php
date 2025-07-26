<?php
// src/modules/contracts/contracts_controller.php (النسخة المصححة والمفصلة)

global $pdo;

// --- 1. الإعدادات والفلترة ---
$records_per_page_options = [10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : 10;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

$filter_q = $_GET['q'] ?? null;
$filter_client_id = $_GET['client_id'] ?? null;
$filter_property_id = $_GET['property_id'] ?? null;
$filter_status = $_GET['status'] ?? null;

// --- 2. بناء جملة الاستعلام الرئيسية وتطبيق الأمان ---
$sql_from_joins = "
    FROM contracts_rental cr
    LEFT JOIN clients c ON cr.client_id = c.id
    LEFT JOIN contract_units cu ON cr.id = cu.contract_id
    LEFT JOIN units u ON cu.unit_id = u.id
    LEFT JOIN properties p ON u.property_id = p.id
";
$sql_where = " WHERE cr.deleted_at IS NULL ";
$params = [];

if ($_SESSION['user_branch_ids'] !== 'ALL' && !empty($_SESSION['user_branch_ids'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['user_branch_ids']), '?'));
    $sql_where .= " AND p.branch_id IN ($placeholders) ";
    foreach ($_SESSION['user_branch_ids'] as $branch_id) { $params[] = $branch_id; }
}

if (!empty($filter_q)) { $search_term = '%' . $filter_q . '%'; $sql_where .= " AND (cr.contract_number LIKE ? OR c.client_name LIKE ?) "; array_push($params, $search_term, $search_term); }
if (!empty($filter_client_id)) { $sql_where .= " AND cr.client_id = ? "; $params[] = $filter_client_id; }
if (!empty($filter_property_id)) { $sql_where .= " AND p.id = ? "; $params[] = $filter_property_id; }
if (!empty($filter_status)) { $sql_where .= " AND cr.status = ? "; $params[] = $filter_status; }

// --- 3. جلب الإحصائيات المفصلة ---
$stats_sql = "
    SELECT
        COUNT(DISTINCT cr.id) as total_contracts,
        SUM(CASE WHEN cr.status = 'Active' THEN 1 ELSE 0 END) as active_contracts,
        SUM(CASE WHEN cr.status = 'Expired' THEN 1 ELSE 0 END) as expired_contracts,
        SUM(CASE WHEN cr.status = 'Draft' THEN 1 ELSE 0 END) as draft_contracts,
        SUM(CASE WHEN cr.status = 'Active' THEN cr.total_amount ELSE 0 END) as active_contracts_value,
        (SELECT COUNT(DISTINCT cu_inner.unit_id) FROM contract_units cu_inner JOIN contracts_rental cr_inner ON cu_inner.contract_id = cr_inner.id WHERE cr_inner.status = 'Active' AND cr_inner.deleted_at IS NULL) as active_rented_units,
        (SELECT SUM(u_inner.area) FROM units u_inner JOIN contract_units cu_inner ON u_inner.id = cu_inner.unit_id JOIN contracts_rental cr_inner ON cu_inner.contract_id = cr_inner.id WHERE cr_inner.status = 'Active' AND cr_inner.deleted_at IS NULL) as active_rented_area,
        (SELECT COUNT(DISTINCT id) FROM clients WHERE deleted_at IS NULL) as total_clients,
        (SELECT COUNT(DISTINCT id) FROM clients WHERE deleted_at IS NULL AND client_type='منشأة') as company_clients,
        (SELECT COUNT(DISTINCT id) FROM clients WHERE deleted_at IS NULL AND client_type='فرد') as individual_clients
    FROM contracts_rental cr
";
$stats = $pdo->query($stats_sql)->fetch(PDO::FETCH_ASSOC);


// --- 4. جلب البيانات الرئيسية (مع التصحيح المطلوب) ---
$data_sql = "
    SELECT 
        cr.*, 
        c.client_name,
        GROUP_CONCAT(DISTINCT p.property_name SEPARATOR ', ') as property_names,
        COUNT(DISTINCT u.id) as units_count,
        GROUP_CONCAT(CONCAT(u.unit_name, ' (', u.unit_type, ')') SEPARATOR '<br>') as unit_details,
        lo.option_value as status_name,
        lo.bg_color as status_color,
        (
            CASE 
                WHEN cr.payment_cycle = 'دفعة واحدة' THEN cr.total_amount
                WHEN cr.payment_cycle = 'شهري' THEN cr.total_amount / GREATEST(1, TIMESTAMPDIFF(MONTH, cr.start_date, cr.end_date) + 1)
                WHEN cr.payment_cycle = 'ربع سنوي' THEN cr.total_amount / GREATEST(1, CEIL((TIMESTAMPDIFF(MONTH, cr.start_date, cr.end_date) + 1) / 3))
                WHEN cr.payment_cycle = 'نصف سنوي' THEN cr.total_amount / GREATEST(1, CEIL((TIMESTAMPDIFF(MONTH, cr.start_date, cr.end_date) + 1) / 6))
                WHEN cr.payment_cycle = 'سنوي' THEN cr.total_amount / GREATEST(1, CEIL((TIMESTAMPDIFF(MONTH, cr.start_date, cr.end_date) + 1) / 12))
                ELSE 0 
            END
        ) as payment_amount
    FROM contracts_rental cr
    LEFT JOIN clients c ON cr.client_id = c.id
    LEFT JOIN contract_units cu ON cr.id = cu.contract_id
    LEFT JOIN units u ON cu.unit_id = u.id
    LEFT JOIN properties p ON u.property_id = p.id
    LEFT JOIN lookup_options lo ON cr.status = lo.option_key AND lo.group_key = 'status'
    {$sql_where}
    GROUP BY cr.id
    ORDER BY cr.id DESC
    LIMIT {$limit} OFFSET {$offset}
";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$contracts = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 5. حساب الإجمالي وترقيم الصفحات ---
$total_records_stmt = $pdo->prepare("SELECT COUNT(DISTINCT cr.id) {$sql_from_joins} {$sql_where}");
$total_records_stmt->execute($params);
$total_records = $total_records_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// --- 6. جلب بيانات الفلاتر ---
$clients_for_filter = $pdo->query("SELECT id, client_name FROM clients WHERE deleted_at IS NULL ORDER BY client_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
$properties_for_filter = $pdo->query("SELECT id, property_name FROM properties WHERE deleted_at IS NULL ORDER BY property_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
$statuses_for_filter = get_lookup_options($pdo, 'status', true);
$statuses_map = [];
foreach($pdo->query("SELECT option_key, option_value, bg_color FROM lookup_options WHERE group_key = 'status'") as $row) {
    $statuses_map[$row['option_key']] = ['name' => $row['option_value'], 'bg_color' => $row['bg_color']];
}

// --- 7. استدعاء الواجهة ---
require_once __DIR__ . '/contracts_view.php';
?>