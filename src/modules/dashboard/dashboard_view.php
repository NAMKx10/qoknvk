<?php
// src/modules/dashboard/dashboard_view.php (النسخة المطورة والاحترافية)

// --- 1. جلب الإحصائيات الشاملة ---
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(id) FROM properties WHERE deleted_at IS NULL) as total_properties,
        (SELECT COUNT(id) FROM units WHERE deleted_at IS NULL) as total_units,
        (SELECT COUNT(id) FROM clients WHERE deleted_at IS NULL) as total_clients,
        (SELECT COUNT(id) FROM contracts_rental WHERE deleted_at IS NULL AND status = 'نشط') as active_contracts
")->fetch(PDO::FETCH_ASSOC);


// --- 2. جلب التنبيهات المهمة (بيانات حقيقية) ---
$thirty_days_later = date('Y-m-d', strtotime('+30 days'));

// وثائق على وشك الانتهاء
$expiring_docs = $pdo->query("
    SELECT id, document_name, expiry_date, document_type 
    FROM documents 
    WHERE deleted_at IS NULL AND expiry_date BETWEEN CURDATE() AND '{$thirty_days_later}'
    ORDER BY expiry_date ASC
")->fetchAll(PDO::FETCH_ASSOC);

// عقود على وشك الانتهاء
$expiring_contracts = $pdo->query("
    SELECT id, contract_number, end_date
    FROM contracts_rental
    WHERE deleted_at IS NULL AND status = 'نشط' AND end_date BETWEEN CURDATE() AND '{$thirty_days_later}'
    ORDER BY end_date ASC
")->fetchAll(PDO::FETCH_ASSOC);


// --- 3. جلب بيانات الرسم البياني (بيانات حقيقية - عقود جديدة شهرياً) ---
$chart_data_stmt = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(id) as count
    FROM contracts_rental
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC
");
$chart_raw_data = $chart_data_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$chart_labels = [];
$chart_series = [];
for ($i = 5; $i >= 0; $i--) {
    $month_key = date('Y-m', strtotime("-$i month"));
    $month_name = date('F', strtotime("-$i month"));
    $chart_labels[] = $month_name;
    $chart_series[] = $chart_raw_data[$month_key] ?? 0;
}
?>

<!-- ========================================================== -->
<!-- الواجهة الجديدة للوحة التحكم (v2.0 - Dynamic & Engaging) -->
<!-- ========================================================== -->

<div class="page-header">
  <div class="row align-items-center">
    <div class="col">
      <div class="page-pretitle">لوحة التحكم</div>
      <h2 class="page-title">نظرة عامة على النظام</h2>
    </div>
    <div class="col-auto ms-auto">
      <div class="btn-list">
        <a href="index.php?page=settings/lookups" class="btn"><i class="ti ti-settings-cog me-2"></i>الإعدادات</a>
        <a href="index.php?page=properties/add" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=properties/add&view_only=true">
            <i class="ti ti-plus me-2"></i>إضافة عقار
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row row-deck row-cards">
  
  <!-- بطاقات الإحصائيات المطورة -->
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">العقارات</div>
        </div>
        <div class="h1 mb-3"><?= $stats['total_properties'] ?? 0 ?></div>
        <div class="d-flex mb-2">
          <div>معدل النمو</div>
          <div class="ms-auto"><span class="text-green d-inline-flex align-items-center lh-1"><i class="ti ti-trending-up me-1"></i> +2%</span></div>
        </div>
        <div class="progress progress-sm">
          <div class="progress-bar bg-primary" style="width: 75%" role="progressbar"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">الوحدات</div>
        </div>
        <div class="h1 mb-3"><?= $stats['total_units'] ?? 0 ?></div>
        <div class="d-flex mb-2">
          <div>نسبة الإشغال</div>
          <div class="ms-auto"><span class="text-red d-inline-flex align-items-center lh-1"><i class="ti ti-trending-down me-1"></i> -1%</span></div>
        </div>
        <div class="progress progress-sm">
          <div class="progress-bar bg-success" style="width: 60%" role="progressbar"></div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">العملاء</div>
        </div>
        <div class="h1 mb-3"><?= $stats['total_clients'] ?? 0 ?></div>
        <div class="d-flex mb-2">
          <div>عملاء جدد هذا الشهر</div>
          <div class="ms-auto"><span class="text-green d-inline-flex align-items-center lh-1"> 5+</span></div>
        </div>
        <div class="progress progress-sm">
          <div class="progress-bar bg-warning" style="width: 45%" role="progressbar"></div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">العقود النشطة</div>
        </div>
        <div class="h1 mb-3"><?= $stats['active_contracts'] ?? 0 ?></div>
        <div class="d-flex mb-2">
          <div>إجمالي العقود</div>
          <div class="ms-auto"><span>156</span></div>
        </div>
        <div class="progress progress-sm">
          <div class="progress-bar bg-danger" style="width: 80%" role="progressbar"></div>
        </div>
      </div>
    </div>
  </div>


  <!-- قسم التنبيهات الجديد -->
  <div class="col-lg-7">
      <div class="card" style="height: 28rem">
          <div class="card-body card-body-scrollable" style="height: 28rem">
              <h2 class="card-title mb-4"><i class="ti ti-bell-ringing-2 text-warning me-2"></i>تنبيهات هامة</h2>
              
              <?php if (empty($expiring_docs) && empty($expiring_contracts)): ?>
                  <div class="text-center text-muted p-5">
                      <i class="ti ti-circle-check" style="font-size: 3rem;"></i>
                      <p class="mt-3">لا توجد تنبيهات عاجلة. كل الأمور على ما يرام!</p>
                  </div>
              <?php endif; ?>

              <?php if (!empty($expiring_docs)): ?>
                  <div class="mb-4">
                      <h4><i class="ti ti-file-alert text-danger me-2"></i>وثائق تنتهي صلاحيتها خلال 30 يوم</h4>
                      <ul class="list-group list-group-flush">
                          <?php foreach($expiring_docs as $doc): ?>
                              <li class="list-group-item d-flex justify-content-between align-items-center">
                                  <div>
                                      <strong><?= htmlspecialchars($doc['document_name']) ?></strong>
                                      <small class="d-block text-muted"><?= htmlspecialchars($doc['document_type']) ?></small>
                                  </div>
                                  <span class="badge bg-danger-lt">ينتهي في: <?= htmlspecialchars($doc['expiry_date']) ?></span>
                              </li>
                          <?php endforeach; ?>
                      </ul>
                  </div>
              <?php endif; ?>

              <?php if (!empty($expiring_contracts)): ?>
                  <div>
                      <h4><i class="ti ti-file-text-alert text-orange me-2"></i>عقود تنتهي خلال 30 يوم</h4>
                      <ul class="list-group list-group-flush">
                          <?php foreach($expiring_contracts as $contract): ?>
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                              <strong>عقد رقم: <?= htmlspecialchars($contract['contract_number']) ?></strong>
                              <span class="badge bg-orange-lt">ينتهي في: <?= htmlspecialchars($contract['end_date']) ?></span>
                          </li>
                          <?php endforeach; ?>
                      </ul>
                  </div>
              <?php endif; ?>

          </div>
      </div>
  </div>


  <!-- قسم الرسم البياني المطور -->
  <div class="col-lg-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">العقود الجديدة (آخر 6 أشهر)</h3>
            <div id="chart-new-contracts" class="w-100" style="height: 24rem;"></div>
        </div>
    </div>
  </div>

</div>

<!-- كود JavaScript للرسم البياني الجديد -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    new ApexCharts(document.getElementById('chart-new-contracts'), {
        chart: { type: "bar", fontFamily: 'inherit', height: '100%', parentHeightOffset: 0, toolbar: { show: false } },
        plotOptions: { bar: { columnWidth: '50%' } },
        dataLabels: { enabled: false },
        fill: { opacity: 1 },
        stroke: { width: 2, curve: 'smooth' },
        grid: { strokeDashArray: 4 },
        xaxis: { labels: { padding: 0 }, tooltip: { enabled: false }, axisBorder: { show: false }, categories: <?= json_encode($chart_labels) ?> },
        yaxis: { labels: { padding: 4 }, min: 0, tickAmount: 5 },
        colors: ["#206bc4"],
        series: [{ name: 'عقود جديدة', data: <?= json_encode($chart_series) ?> }],
    }).render();
});
</script>