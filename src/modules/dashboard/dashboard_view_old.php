<?php
// =================================================================
// 1. جلب البيانات (نفس استعلامات PHP السابقة)
// =================================================================
$stats = $pdo->query("SELECT (SELECT COUNT(*) FROM branches WHERE deleted_at IS NULL AND status = 'نشط') as active_branches, (SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL AND status = 'نشط') as active_properties, (SELECT COUNT(*) FROM units WHERE deleted_at IS NULL AND status = 'متاحة') as available_units, (SELECT COUNT(*) FROM clients WHERE deleted_at IS NULL AND status = 'نشط') as active_clients")->fetch(PDO::FETCH_ASSOC);
$expiring_contracts = $pdo->query("SELECT cr.id, cr.contract_number, cr.end_date, c.client_name, DATEDIFF(cr.end_date, CURDATE()) as days_left FROM contracts_rental cr JOIN clients c ON cr.client_id = c.id WHERE cr.deleted_at IS NULL AND cr.status = 'نشط' AND cr.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY cr.end_date ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$late_payments = $pdo->query("SELECT ps.id, ps.due_date, (ps.amount_due - ps.amount_paid) as remaining, CASE WHEN ps.contract_type = 'rental' THEN (SELECT c.client_name FROM clients c JOIN contracts_rental cr ON c.id = cr.client_id WHERE cr.id = ps.contract_id) WHEN ps.contract_type = 'supply' THEN (SELECT s.supplier_name FROM suppliers s JOIN contracts_supply cs ON s.id = cs.supplier_id WHERE cs.id = ps.contract_id) END as party_name FROM payment_schedules ps WHERE ps.status != 'مدفوع بالكامل' AND ps.due_date < CURDATE() ORDER BY ps.due_date ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$units_status_data = $pdo->query("SELECT status, COUNT(*) as count FROM units WHERE deleted_at IS NULL GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$chart_labels = json_encode(array_values($units_status_data_keys = array_keys($units_status_data)));
$chart_values = json_encode(array_values($units_status_data));

// تخصيص ألوان الرسم البياني لتكون أكثر جمالاً
$chart_colors = [];
foreach ($units_status_data_keys as $key) {
    if ($key == 'متاحة') $chart_colors[] = 'rgba(25, 135, 84, 0.8)'; // أخضر
    elseif ($key == 'مؤجرة') $chart_colors[] = 'rgba(255, 193, 7, 0.8)'; // أصفر
    elseif ($key == 'ملغاة') $chart_colors[] = 'rgba(220, 53, 69, 0.8)'; // أحمر
    else $chart_colors[] = 'rgba(108, 117, 125, 0.8)'; // رمادي
}
$chart_colors_json = json_encode($chart_colors);

?>

<!-- تحميل مكتبة الرسوم البيانية -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- CSS مدمج لتطبيق التصميم الجديد -->
<style>
    .dashboard-hero {
        background: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=2070&auto=format&fit=crop') center center/cover no-repeat;
        position: relative;
        padding: 4rem 2rem;
        border-radius: 0.75rem;
        margin-bottom: 2rem;
    }
    .hero-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(6, 47, 79, 0.75);
        border-radius: 0.75rem;
    }
    .hero-content {
        position: relative;
        z-index: 2;
        color: white;
    }
    .stat-card-transparent {
        background-color: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        text-align: center;
        border-radius: 0.5rem;
    }
    .stat-card-transparent .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
    }
    .stat-card-transparent .stat-title {
        font-size: 1rem;
        opacity: 0.9;
    }
    .card-dashboard {
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0,0.07);
        border-radius: 0.75rem;
    }
</style>

<!-- بداية عرض الواجهة -->

<!-- 1. الهيدر الرئيسي -->
<div class="dashboard-hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="display-5 fw-bold">لوحة التحكم</h1>
        <p class="lead">مرحباً بك، <?php echo htmlspecialchars($_SESSION['username']); ?>. إليك ملخص حيوي للنظام.</p>
        <hr class="border-light">
        <!-- صف الإحصائيات داخل الهيدر -->
        <div class="row g-3 mt-3">
            <div class="col-lg-3 col-6"><div class="stat-card-transparent p-3"><div class="stat-number"><?= $stats['active_branches'] ?? 0 ?></div><div class="stat-title">الفروع النشطة</div></div></div>
            <div class="col-lg-3 col-6"><div class="stat-card-transparent p-3"><div class="stat-number"><?= $stats['active_properties'] ?? 0 ?></div><div class="stat-title">العقارات النشطة</div></div></div>
            <div class="col-lg-3 col-6"><div class="stat-card-transparent p-3"><div class="stat-number"><?= $stats['available_units'] ?? 0 ?></div><div class="stat-title">الوحدات المتاحة</div></div></div>
            <div class="col-lg-3 col-6"><div class="stat-card-transparent p-3"><div class="stat-number"><?= $stats['active_clients'] ?? 0 ?></div><div class="stat-title">العملاء النشطين</div></div></div>
        </div>
    </div>
</div>

<!-- 2. التنبيهات والرسوم البيانية -->
<div class="row g-4">
    <!-- عمود التنبيهات -->
    <div class="col-lg-7">
        <div class="card card-dashboard h-100">
            <div class="card-body">
                <h5 class="card-title mb-4"><i class="fas fa-bell text-warning me-2"></i>تنبيهات وإجراءات عاجلة</h5>
                
                <h6><i class="fas fa-file-signature text-danger me-2"></i>عقود تنتهي قريباً</h6>
                <div class="list-group list-group-flush mb-4">
                    <?php if(empty($expiring_contracts)): ?>
                        <div class="list-group-item text-muted">لا توجد عقود تنتهي خلال 30 يوم.</div>
                    <?php else: foreach($expiring_contracts as $contract): ?>
                        <a href="index.php?page=contracts/view&id=<?= $contract['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div><?= htmlspecialchars($contract['client_name']) ?> <small class="text-muted">(عقد <?= htmlspecialchars($contract['contract_number']) ?>)</small></div>
                            <span class="badge bg-danger rounded-pill">باقي <?= $contract['days_left'] ?> يوم</span>
                        </a>
                    <?php endforeach; endif; ?>
                </div>

                <h6><i class="fas fa-money-bill-wave text-danger me-2"></i>دفعات متأخرة</h6>
                <div class="list-group list-group-flush">
                     <?php if(empty($late_payments)): ?>
                        <div class="list-group-item text-muted">لا توجد دفعات متأخرة.</div>
                    <?php else: foreach($late_payments as $payment): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($payment['party_name']) ?> (متبقي: <strong class="text-danger"><?= number_format($payment['remaining'], 2) ?></strong>)</span>
                            <small class="text-muted">مستحقة منذ <?= $payment['due_date'] ?></small>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- عمود الرسوم البيانية -->
    <div class="col-lg-5">
        <div class="card card-dashboard h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-4"><i class="fas fa-chart-pie me-2 text-primary"></i>توزيع حالات الوحدات</h5>
                <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="min-height: 280px;">
                    <canvas id="unitsStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('unitsStatusChart')) {
        const ctx = document.getElementById('unitsStatusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?= $chart_labels ?>,
                datasets: [{
                    label: 'عدد الوحدات',
                    data: <?= $chart_values ?>,
                    backgroundColor: <?= $chart_colors_json ?>,
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20, font: { size: 14 } }
                    }
                }
            }
        });
    }
});
</script>