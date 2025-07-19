<?php
// src/modules/units/units_controller.php (النسخة النهائية التي تعمل مع البيانات العربية)

global $pdo;

// 1. الإعدادات والترقيم
$records_per_page_options = [10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $records_per_page_options) ? (int)$_GET['limit'] : 10;
$current_page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$offset = ($current_page - 1) * $limit;

// 2. الفلاتر
$filter_q = $_GET['q'] ?? null;
$filter_property_id = $_GET['property_id'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_status = $_GET['status'] ?? null; // يستقبل المفتاح الإنجليزي مثل 'Available'

// 3. جلب بيانات الفلاتر أولاً (مهم للترجمة)
$properties_for_filter = $pdo->query("SELECT id, property_name FROM properties WHERE deleted_at IS NULL ORDER BY property_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
$unit_types_for_filter = get_lookup_options($pdo, 'unit_type');
$statuses_for_filter = get_lookup_options($pdo, 'status', true); // يجلب ['Available' => 'متاحة']

// 4. بناء جملة الاستعلام
$sql_where = " WHERE u.deleted_at IS NULL ";
$params = [];
$sql_where .= build_branches_query_condition('p', $params);

if (!empty($filter_q)) {
    $search_term = '%' . $filter_q . '%';
    $sql_where .= " AND (u.unit_name LIKE ? OR u.unit_code LIKE ?) ";
    array_push($params, $search_term, $search_term);
}
if (!empty($filter_property_id)) { $sql_where .= " AND u.property_id = ? "; $params[] = $filter_property_id; }
if (!empty($filter_type)) { $sql_where .= " AND u.unit_type = ? "; $params[] = $filter_type; }

// ✨ التصحيح الحاسم لفلتر الحالة: ترجمة المفتاح الإنجليزي إلى القيمة العربية ✨
if (!empty($filter_status) && isset($statuses_for_filter[$filter_status])) {
    $sql_where .= " AND u.status = ? ";
    $params[] = $statuses_for_filter[$filter_status]; // البحث بالقيمة العربية 'متاحة'
}

// 5. جلب الإحصائيات (بالطريقة الصحيحة التي تستخدم النصوص العربية)
$stats_params = $params;
$stats_sql = "
    SELECT 
        COUNT(u.id) as total_units,
        SUM(CASE WHEN u.status = 'مؤجر' THEN 1 ELSE 0 END) as rented_units,
        SUM(CASE WHEN u.status = 'متاح' THEN 1 ELSE 0 END) as available_units,
        SUM(u.area) as total_area
    FROM units u
    LEFT JOIN properties p ON u.property_id = p.id
    {$sql_where}
";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute($stats_params);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// 6. جلب البيانات الرئيسية
$total_records = $stats['total_units'] ?? 0;
$total_pages = ceil($total_records / $limit);

// ✨ الاستعلام المحدث بالكامل مع JOIN صحيح على القيمة العربية ✨
$data_sql = "
    SELECT 
        u.*, 
        p.property_name,
        b.branch_code,
        lo.option_value as status_name,
        lo.bg_color as status_color,
        (SELECT c.client_name FROM clients c JOIN contracts_rental cr ON c.id = cr.client_id JOIN contract_units cu ON cr.id = cu.contract_id WHERE cu.unit_id = u.id AND cr.status = 'Active' AND cr.deleted_at IS NULL LIMIT 1) as tenant_name
    FROM units u 
    JOIN properties p ON u.property_id = p.id
    LEFT JOIN branches b ON p.branch_id = b.id
    LEFT JOIN lookup_options lo ON u.status = lo.option_value AND lo.group_key = 'status'
    {$sql_where} 
    ORDER BY u.id DESC
    LIMIT {$limit} OFFSET {$offset}
";
$data_stmt = $pdo->prepare($data_sql);
$data_stmt->execute($params);
$units = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

// 7. استدعاء الواجهة
require_once __DIR__ . '/units_view.php';
?>