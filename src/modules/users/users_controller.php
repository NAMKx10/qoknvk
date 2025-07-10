<?php
// src/modules/users/users_controller.php (العقل الجديد)

global $pdo; // ✨ هذا السطر هو مفتاح الحل ✨
require_once ROOT_PATH . '/src/core/functions.php'; 

// --- 1. الإعدادات والفلترة ---
$limit = $_GET['limit'] ?? 10;
$current_page = $_GET['p'] ?? 1;
$offset = ($current_page - 1) * $limit;

$filter_q = $_GET['q'] ?? null;
$filter_role_id = $_GET['role_id'] ?? null;
$filter_branch_id = $_GET['branch_id'] ?? null;

// --- 2. بناء الاستعلام ---
$joins = "
    LEFT JOIN roles r ON u.role_id = r.id
    LEFT JOIN user_branches ub ON u.id = ub.user_id
    LEFT JOIN branches b ON ub.branch_id = b.id
";
$where_conditions = ["u.deleted_at IS NULL"];
$params = [];

if (!empty($filter_q)) {
    $where_conditions[] = "(u.full_name LIKE :q OR u.username LIKE :q OR u.email LIKE :q)";
    $params[':q'] = '%' . $filter_q . '%';
}
if (!empty($filter_role_id)) {
    $where_conditions[] = "u.role_id = :role_id";
    $params[':role_id'] = $filter_role_id;
}
if (!empty($filter_branch_id)) {
    $where_conditions[] = "u.id IN (SELECT user_id FROM user_branches WHERE branch_id = :branch_id)";
    $params[':branch_id'] = $filter_branch_id;
}

$sql_where = " WHERE " . implode(" AND ", $where_conditions);

// --- 3. جلب الإحصائيات ---
$stats_sql = "SELECT (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) as total, (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL AND status = 'Active') as active";
$stats = $pdo->query($stats_sql)->fetch(PDO::FETCH_ASSOC);
$stats['inactive'] = $stats['total'] - $stats['active'];

// --- 4. جلب البيانات الرئيسية ---
$count_query = "SELECT COUNT(DISTINCT u.id) FROM users u {$joins} {$sql_where}";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

if ($total_records > 0) {
    $data_query = "
        SELECT DISTINCT u.id, u.full_name, u.username, u.email, u.status, r.role_name, 
               (SELECT GROUP_CONCAT(b_inner.branch_code SEPARATOR ', ') 
                FROM user_branches ub_inner 
                JOIN branches b_inner ON ub_inner.branch_id = b_inner.id 
                WHERE ub_inner.user_id = u.id) as branch_codes
        FROM users u {$joins} {$sql_where}
        GROUP BY u.id
        ORDER BY u.id ASC 
        LIMIT {$limit} OFFSET {$offset}
    ";
    $data_stmt = $pdo->prepare($data_query);
    $data_stmt->execute($params);
    $users = $data_stmt->fetchAll();
} else {
    $users = [];
}

// --- 5. جلب بيانات الفلاتر (التي تحتاجها الواجهة) ---
$roles_list = $pdo->query("SELECT id, role_name FROM roles WHERE deleted_at IS NULL ORDER BY role_name")->fetchAll();
$branches_list = $pdo->query("SELECT id, branch_name FROM branches WHERE deleted_at IS NULL ORDER BY branch_name")->fetchAll();

// --- (جديد ومصحح) جلب بيانات حالات المستخدمين لـ الواجهة ---
$stmt = $pdo->prepare("SELECT option_key, option_value, color, bg_color FROM lookup_options WHERE group_key = 'status'");
$stmt->execute();
$statuses_options = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statuses_lookup = [];
foreach ($statuses_options as $option) {
    $statuses_lookup[$option['option_key']] = [
        'option_value' => $option['option_value'],
        'color' => $option['color'],
        'bg_color' => $option['bg_color']
    ];
}

// --- 6. استدعاء الواجهة ---
require_once __DIR__ . '/users_view.php';
?>
