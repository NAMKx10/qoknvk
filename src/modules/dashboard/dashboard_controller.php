<?php
// src/modules/dashboard/dashboard_controller.php

global $pdo;

try {
    // 1. جلب الإحصائيات الشاملة في استعلام واحد
    $stats_query = "
        SELECT
            (SELECT COUNT(id) FROM properties WHERE deleted_at IS NULL) as total_properties,
            (SELECT COUNT(id) FROM units WHERE deleted_at IS NULL) as total_units,
            (SELECT COUNT(id) FROM units WHERE deleted_at IS NULL AND status = 'Rented') as rented_units,
            (SELECT COUNT(id) FROM clients WHERE deleted_at IS NULL) as total_clients,
            (SELECT COUNT(id) FROM documents WHERE deleted_at IS NULL) as total_documents,
            (SELECT COUNT(id) FROM contracts_rental WHERE deleted_at IS NULL) as total_contracts,
            (SELECT COUNT(id) FROM contracts_rental WHERE deleted_at IS NULL AND status = 'Active') as active_contracts,
            (SELECT COALESCE(SUM(total_amount/12), 0) FROM contracts_rental WHERE deleted_at IS NULL AND status = 'Active') as monthly_revenue
    ";
    $stats = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC);

    // حساب النسب
    $stats['available_units'] = $stats['total_units'] - $stats['rented_units'];
    $stats['occupancy_rate'] = $stats['total_units'] > 0 ? round(($stats['rented_units'] / $stats['total_units']) * 100, 1) : 0;
    
    // 2. جلب التنبيهات الذكية
    $thirty_days_later = date('Y-m-d', strtotime('+30 days'));
    $today = date('Y-m-d');

    $alerts = [
        'expiring_contracts' => $pdo->query("SELECT COUNT(*) FROM contracts_rental WHERE deleted_at IS NULL AND status = 'Active' AND end_date BETWEEN '{$today}' AND '{$thirty_days_later}'")->fetchColumn(),
        'expiring_documents' => $pdo->query("SELECT COUNT(*) FROM documents WHERE deleted_at IS NULL AND expiry_date BETWEEN '{$today}' AND '{$thirty_days_later}'")->fetchColumn(),
        'overdue_payments' => $pdo->query("SELECT COUNT(*) FROM payment_schedules WHERE status != 'مدفوع بالكامل' AND due_date < '{$today}'")->fetchColumn()
    ];

    // 3. جلب آخر الأنشطة
    $recent_activities = $pdo->query("
        (SELECT 'contract' as type, contract_number as title, created_at, 'ti ti-file-text' as icon, 'blue' as color FROM contracts_rental WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 3)
        UNION ALL
        (SELECT 'property' as type, property_name as title, created_at, 'ti ti-building' as icon, 'green' as color FROM properties WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 3)
        UNION ALL
        (SELECT 'client' as type, client_name as title, created_at, 'ti ti-user-plus' as icon, 'yellow' as color FROM clients WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 3)
        ORDER BY created_at DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 4. جلب بيانات الرسوم البيانية
    $chart_labels = [];
    $chart_series = [];
    for ($i = 5; $i >= 0; $i--) {
        $month_key = date('Y-m', strtotime("-$i month"));
        $month_name = date('M', strtotime("-$i month"));
        $chart_labels[] = $month_name;
        
        $stmt = $pdo->prepare("SELECT COUNT(id) FROM contracts_rental WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmt->execute([$month_key]);
        $chart_series[] = $stmt->fetchColumn();
    }
    
} catch (Exception $e) {
    // في حالة حدوث خطأ، نعرض قيم افتراضية لتجنب انهيار الصفحة
    $stats = array_fill_keys(['total_properties', 'total_units', 'rented_units', 'available_units', 'total_contracts', 'active_contracts', 'total_clients', 'total_documents', 'occupancy_rate', 'monthly_revenue'], 0);
    $alerts = array_fill_keys(['expiring_contracts', 'expiring_documents', 'overdue_payments'], 0);
    $recent_activities = [];
    $chart_labels = [];
    $chart_series = [];
}

// استدعاء الواجهة
require_once __DIR__ . '/dashboard_view.php';
?>