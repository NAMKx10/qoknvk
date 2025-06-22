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

<div class="page-header">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">لوحة التحكم</h2>
      <div class="text-muted mt-1">آخر تحديث: <?= date('Y-m-d H:i') ?></div>
    </div>
    <div class="col-auto ms-auto">
      <div class="btn-list">
        <a href="#" class="btn btn-primary"><i class="ti ti-report-money me-2"></i>عرض التقارير</a>
      </div>
    </div>
  </div>
</div>

<div class="row row-deck row-cards">
  <div class="col-sm-6 col-lg-3">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto"><span class="bg-primary text-white avatar"><i class="ti ti-building-community"></i></span></div>
          <div class="col">
            <div class="font-weight-medium">5 العقارات</div>
            <div class="text-muted">3 منها نشطة</div>
          </div>
        </div>
      </div>
    </div>
  </div>
   <div class="col-sm-6 col-lg-3">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto"><span class="bg-green text-white avatar"><i class="ti ti-door"></i></span></div>
          <div class="col">
            <div class="font-weight-medium">12 وحدة</div>
            <div class="text-muted">6 منها متاحة</div>
          </div>
        </div>
      </div>
    </div>
  </div>
   <div class="col-sm-6 col-lg-3">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto"><span class="bg-twitter text-white avatar"><i class="ti ti-users"></i></span></div>
          <div class="col">
            <div class="font-weight-medium">11 عميل</div>
            <div class="text-muted">10 منهم نشطين</div>
          </div>
        </div>
      </div>
    </div>
  </div>
   <div class="col-sm-6 col-lg-3">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto"><span class="bg-facebook text-white avatar"><i class="ti ti-truck"></i></span></div>
          <div class="col">
            <div class="font-weight-medium">5 موردين</div>
            <div class="text-muted">جميعهم نشطين</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">إيرادات ومصروفات آخر 6 أشهر</h3>
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