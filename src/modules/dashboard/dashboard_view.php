<?php
// Dashboard Next-Gen by ناجي (the best!)

// 1. جلب الإحصائيات الشاملة والذكية
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(id) FROM properties WHERE deleted_at IS NULL) as total_properties,
        (SELECT COUNT(id) FROM units WHERE deleted_at IS NULL) as total_units,
        (SELECT COUNT(id) FROM clients WHERE deleted_at IS NULL) as total_clients,
        (SELECT COUNT(id) FROM contracts_rental WHERE deleted_at IS NULL AND status = 'نشط') as active_contracts,
        (SELECT COUNT(id) FROM contracts_rental WHERE deleted_at IS NULL) as total_contracts
")->fetch(PDO::FETCH_ASSOC);

// 2. جلب التنبيهات الذكية
$thirty_days_later = date('Y-m-d', strtotime('+30 days'));
$today = date('Y-m-d');

// وثائق على وشك الانتهاء أو منتهية
$expiring_docs = $pdo->query("
    SELECT id, document_name, expiry_date, document_type 
    FROM documents 
    WHERE deleted_at IS NULL AND expiry_date IS NOT NULL AND expiry_date <= '{$thirty_days_later}'
    ORDER BY expiry_date ASC
")->fetchAll(PDO::FETCH_ASSOC);

// عقود على وشك الانتهاء أو منتهية
$expiring_contracts = $pdo->query("
    SELECT id, contract_number, end_date
    FROM contracts_rental
    WHERE deleted_at IS NULL AND status = 'نشط' AND end_date <= '{$thirty_days_later}'
    ORDER BY end_date ASC
")->fetchAll(PDO::FETCH_ASSOC);

// دفعات متأخرة (بيانات حقيقية)
$delayed_payments_query = "
    SELECT ps.id, ps.contract_id, ps.due_date, ps.amount_due,
           cr.contract_number
    FROM payment_schedules ps
    JOIN contracts_rental cr ON ps.contract_id = cr.id AND ps.contract_type = 'rental'
    WHERE 
        ps.status != 'مدفوع بالكامل' 
        AND ps.due_date < '{$today}'
        AND cr.deleted_at IS NULL
    ORDER BY ps.due_date ASC
    LIMIT 5
";
$delayed_payments = $pdo->query($delayed_payments_query)->fetchAll(PDO::FETCH_ASSOC);

// 3. جلب بيانات الرسوم البيانية (عقود جديدة، نمو العملاء)
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

// --- Widgets/Components يمكن وضعها في ملفات منفصلة لاحقاً ---

?>

<div class="page-header">
  <div class="row align-items-center">
    <div class="col">
      <div class="page-pretitle">لوحة التحكم المتقدمة</div>
      <h2 class="page-title">التحكم الشامل وإحصائيات الأداء</h2>
    </div>
    <div class="col-auto ms-auto">
      <div class="btn-list">
        <a href="index.php?page=settings/lookups" class="btn"><i class="ti ti-settings-cog me-2"></i>الإعدادات</a>
        <a href="index.php?page=properties/add" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=properties/add&view_only=true">
            <i class="ti ti-plus me-2"></i>إضافة عقار
        </a>
        <a href="index.php?page=documents/add" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=documents/add&view_only=true">
            <i class="ti ti-file-plus me-2"></i>إضافة وثيقة
        </a>
        <a href="index.php?page=clients/add" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#main-modal" data-bs-url="index.php?page=clients/add&view_only=true">
            <i class="ti ti-user-plus me-2"></i>إضافة عميل
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row row-deck row-cards">

  <!-- بطاقات الإحصائيات الديناميكية -->
  <div class="col-sm-6 col-lg-3">
    <div class="card card-animate">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">العقارات</div>
        </div>
        <div class="h1 mb-3 animated-counter" data-value="<?= $stats['total_properties'] ?? 0 ?>">0</div>
        <div class="d-flex mb-2">
          <div>معدل النمو الشهري</div>
          <div class="ms-auto"><span class="text-green d-inline-flex align-items-center lh-1"><i class="ti ti-trending-up me-1"></i> +3.1%</span></div>
        </div>
        <div class="progress progress-sm">
          <div class="progress-bar bg-primary" style="width: 78%" role="progressbar"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card card-animate">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">الوحدات</div>
        </div>
        <div class="h1 mb-3 animated-counter" data-value="<?= $stats['total_units'] ?? 0 ?>">0</div>
        <div class="d-flex mb-2">
          <div>نسبة الإشغال</div>
          <div class="ms-auto"><span class="text-yellow d-inline-flex align-items-center lh-1"><i class="ti ti-trending-up me-1"></i> 85%</span></div>
        </div>
        <div class="progress progress-sm">
          <div class="progress-bar bg-success" style="width: 85%" role="progressbar"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card card-animate">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">العملاء</div>
        </div>
        <div class="h1 mb-3 animated-counter" data-value="<?= $stats['total_clients'] ?? 0 ?>">0</div>
        <div class="d-flex mb-2">
          <div>عملاء جدد هذا الشهر</div>
          <div class="ms-auto"><span class="text-green d-inline-flex align-items-center lh-1">+7</span></div>
        </div>
        <div class="progress progress-sm">
          <div class="progress-bar bg-warning" style="width: 62%" role="progressbar"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card card-animate">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">العقود النشطة</div>
        </div>
        <div class="h1 mb-3 animated-counter" data-value="<?= $stats['active_contracts'] ?? 0 ?>">0</div>
        <div class="d-flex mb-2">
          <div>إجمالي العقود</div>
          <div class="ms-auto"><span><?= $stats['total_contracts'] ?? 0 ?></span></div>
        </div>
        <div class="progress progress-sm">
          <div class="progress-bar bg-danger" style="width: 82%" role="progressbar"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- قسم التنبيهات الذكية -->
  <div class="col-lg-6">
    <div class="card card-animate" style="height: 28rem;">
      <div class="card-body card-body-scrollable" style="height: 28rem;">
        <h2 class="card-title mb-4"><i class="ti ti-bell-ringing-2 text-warning me-2"></i>تنبيهات ذكية</h2>
        <div class="mb-3">
          <input type="text" class="form-control" id="alert-filter" placeholder="فلترة حسب النص أو النوع...">
        </div>
        <div id="alerts-list">
        <?php if (empty($expiring_docs) && empty($expiring_contracts) && empty($delayed_payments)): ?>
          <div class="text-center text-muted p-5">
            <i class="ti ti-circle-check" style="font-size: 3rem;"></i>
            <p class="mt-3">لا توجد تنبيهات عاجلة. كل شيء على ما يرام!</p>
          </div>
        <?php endif; ?>

        <?php foreach($expiring_docs as $doc): ?>
          <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert" data-type="doc">
            <i class="ti ti-file-alert me-2"></i>
            <div>
              <strong><?= htmlspecialchars($doc['document_name']) ?></strong>
              <span class="d-block small text-muted"><?= htmlspecialchars($doc['document_type']) ?></span>
              <span class="badge bg-danger-lt ms-2">تنتهي في: <?= htmlspecialchars($doc['expiry_date']) ?></span>
            </div>
            <button type="button" class="btn-close ms-auto dismiss-alert" data-bs-dismiss="alert" aria-label="إغلاق"></button>
          </div>
        <?php endforeach; ?>

        <?php foreach($expiring_contracts as $contract): ?>
          <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert" data-type="contract">
            <i class="ti ti-file-text-alert me-2"></i>
            <div>
              <strong>عقد رقم: <?= htmlspecialchars($contract['contract_number']) ?></strong>
              <span class="badge bg-orange-lt ms-2">ينتهي في: <?= htmlspecialchars($contract['end_date']) ?></span>
            </div>
            <button type="button" class="btn-close ms-auto dismiss-alert" data-bs-dismiss="alert" aria-label="إغلاق"></button>
          </div>
        <?php endforeach; ?>

        <?php foreach($delayed_payments as $pay): ?>
          <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert" data-type="payment">
            <i class="ti ti-credit-card me-2"></i>
            <div>
              <strong>دفعة متأخرة للعقد #<?= htmlspecialchars($pay['contract_id']) ?></strong>
              <span class="badge bg-info-lt ms-2">تاريخ الاستحقاق: <?= htmlspecialchars($pay['due_date']) ?></span>
              <span class="badge bg-secondary ms-2"><?= htmlspecialchars($pay['amount_due']) ?> ﷼</span>
            </div>
            <button type="button" class="btn-close ms-auto dismiss-alert" data-bs-dismiss="alert" aria-label="إغلاق"></button>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- قسم الرسوم البيانية المتطورة -->
  <div class="col-lg-6">
    <div class="card card-animate">
      <div class="card-body">
        <h3 class="card-title">العقود الجديدة (آخر 6 أشهر)</h3>
        <div class="mb-3">
          <button class="btn btn-outline-primary btn-sm me-2" id="chart-bar-btn">أعمدة</button>
          <button class="btn btn-outline-secondary btn-sm" id="chart-donut-btn">دونات</button>
        </div>
        <div id="chart-new-contracts" class="w-100" style="height: 22rem;"></div>
      </div>
    </div>
  </div>
</div>


<script>
// عدادات متحركة
document.querySelectorAll('.animated-counter').forEach(function(el) {
  let end = parseInt(el.getAttribute('data-value')) || 0;
  let current = 0;
  let inc = Math.max(1, Math.ceil(end / 50));
  let interval = setInterval(function() {
    current += inc;
    if (current >= end) {
      el.textContent = end;
      clearInterval(interval);
    } else {
      el.textContent = current;
    }
  }, 15);
});

// فلترة التنبيهات
document.getElementById('alert-filter').addEventListener('input', function() {
  let val = this.value.trim().toLowerCase();
  document.querySelectorAll('#alerts-list .alert').forEach(function(alert) {
    if (alert.textContent.toLowerCase().includes(val)) {
      alert.style.display = '';
    } else {
      alert.style.display = 'none';
    }
  });
});

// رسم بياني متطور مع تبديل (أعمدة/دونات)
let chartData = {
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
};
let chart = new ApexCharts(document.getElementById('chart-new-contracts'), chartData);
chart.render();

document.getElementById('chart-bar-btn').onclick = function() {
  chart.updateOptions({ chart: { type: 'bar' } });
};
document.getElementById('chart-donut-btn').onclick = function() {
  chart.updateOptions({
    chart: { type: 'donut' },
    labels: <?= json_encode($chart_labels) ?>,
    series: <?= json_encode($chart_series) ?>,
    plotOptions: undefined,
    yaxis: undefined,
    xaxis: undefined,
    legend: { position: 'bottom' }
  });
};

// إغلاق تنبيهات (Dismiss/Snooze)
document.querySelectorAll('.dismiss-alert').forEach(function(btn) {
  btn.addEventListener('click', function(e) {
    let alert = btn.closest('.alert');
    if (alert) alert.remove();
  });
});
</script>