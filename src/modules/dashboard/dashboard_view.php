<?php
// 1. جلب الإحصائيات الرئيسية (نفس المنطق من المسار الأول)
$stats = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM branches WHERE deleted_at IS NULL AND status = 'نشط') as active_branches,
        (SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL AND status = 'نشط') as active_properties,
        (SELECT COUNT(*) FROM units WHERE deleted_at IS NULL AND status = 'متاحة') as available_units,
        (SELECT COUNT(*) FROM clients WHERE deleted_at IS NULL AND status = 'نشط') as active_clients
")->fetch(PDO::FETCH_ASSOC);

// 2. جلب بيانات الرسم البياني (مثال)
$chart_data = [
    'labels' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
    'series' => [[20, 35, 60, 45, 70, 85]]
];
?>

<!-- 1. صف بطاقات الإحصائيات -->
<div class="row row-deck row-cards">
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">الفروع النشطة</div>
        </div>
        <div class="h1 mb-3"><?= $stats['active_branches'] ?? 0 ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">العقارات النشطة</div>
        </div>
        <div class="h1 mb-3"><?= $stats['active_properties'] ?? 0 ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">الوحدات المتاحة</div>
        </div>
        <div class="h1 mb-3"><?= $stats['available_units'] ?? 0 ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader">العملاء النشطين</div>
        </div>
        <div class="h1 mb-3"><?= $stats['active_clients'] ?? 0 ?></div>
      </div>
    </div>
  </div>
</div>

<!-- 2. بطاقة الرسم البياني -->
<div class="row row-deck row-cards mt-2">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">إيرادات آخر 6 أشهر (مثال)</h3>
                <div id="chart-revenue-bg" class="w-100" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>


<!-- كود JavaScript لتفعيل الرسم البياني (خاص بـ Tabler) -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    // مكتبة ApexCharts مدمجة في tabler.min.js
    new ApexCharts(document.getElementById('chart-revenue-bg'), {
        chart: {
            type: "area",
            fontFamily: 'inherit',
            height: 300,
            parentHeightOffset: 0,
            toolbar: { show: false },
            animations: { enabled: true },
        },
        dataLabels: { enabled: false },
        fill: {
            opacity: .7,
            type: 'solid'
        },
        stroke: {
            width: 2,
            lineCap: "round",
            curve: "smooth",
        },
        grid: {
            padding: { top: -20, right: 0, left: -4, bottom: -4 },
            strokeDashArray: 4,
        },
        xaxis: {
            labels: { padding: 0 },
            tooltip: { enabled: false },
            axisBorder: { show: false },
            categories: <?= json_encode($chart_data['labels']) ?>,
        },
        yaxis: {
            labels: { padding: 4 }
        },
        series: [{
            name: 'الإيرادات',
            data: <?= json_encode($chart_data['series'][0]) ?>
        }],
    }).render();
});
</script>