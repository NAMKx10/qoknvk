<?php
// جلب اسم النظام من الإعدادات لجعله ديناميكيًا
// لاحظ أننا لم نقم بربط جدول settings بعد، لذلك سنستخدم قيمة افتراضية
$system_name = 'نظام إدارة الأملاك';
?>

<!-- 1. بطاقة الهيدر الرئيسية -->
<div class="card card-md">
    <div class="card-body text-center">
        <i class="ti ti-rocket text-primary" style="font-size: 4rem; margin-bottom: 1rem;"></i>
        <h1 class="card-title mb-1"><?= htmlspecialchars($system_name) ?></h1>
        <p class="text-muted" style="font-size: 1.1rem;">
            منصة متكاملة لإدارة الأصول والعقارات، مصممة لتحويل البيانات إلى قرارات استراتيجية ناجحة.
        </p>
    </div>
</div>

<!-- 2. صف الأهداف والرسالة -->
<div class="row row-cards mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-blue-lt"><i class="ti ti-bulb"></i></span>
                    </div>
                    <div>
                        <h4 class="card-title">فكرتنا وهدفنا</h4>
                        <p class="text-muted mb-0">الانطلاق من مجرد نظام تقليدي إلى مركز عمليات ذكي يوحد كل جوانب الإدارة.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-green-lt"><i class="ti ti-target-arrow"></i></span>
                    </div>
                    <div>
                        <h4 class="card-title">رسالتنا</h4>
                        <p class="text-muted mb-0">تمكين أصحاب القرار بأدوات سريعة، مرنة، وآمنة تمنحهم رؤية شاملة.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. بطاقة خارطة الطريق -->
<div class="card mt-4">
  <div class="card-header">
    <h3 class="card-title"><i class="ti ti-road me-2 text-purple"></i>خارطة الطريق المستقبلية</h3>
  </div>
  <div class="list-group list-group-flush">
    <div class="list-group-item">
      <div class="row align-items-center">
        <div class="col-auto"><span class="badge bg-green-lt">مكتمل</span></div>
        <div class="col-7"><div class="text-truncate">تأسيس البنية التحتية وموديلات الإدارة</div></div>
      </div>
    </div>
    <div class="list-group-item">
      <div class="row align-items-center">
        <div class="col-auto"><span class="badge bg-yellow-lt">قيد التنفيذ</span></div>
        <div class="col-7"><div class="text-truncate">بناء وتطوير الواجهات باستخدام Tabler</div></div>
      </div>
    </div>
    <div class="list-group-item">
      <div class="row align-items-center">
        <div class="col-auto"><span class="badge bg-blue-lt">مستقبلي</span></div>
        <div class="col-7"><div class="text-truncate">بناء المركز المالي المتقدم (فواتير، شيكات)</div></div>
      </div>
    </div>
    <div class="list-group-item">
      <div class="row align-items-center">
        <div class="col-auto"><span class="badge bg-blue-lt">مستقبلي</span></div>
        <div class="col-7"><div class="text-truncate">نظام الملكية المتعددة والترقيم المستقل</div></div>
      </div>
    </div>
    <div class="list-group-item">
      <div class="row align-items-center">
        <div class="col-auto"><span class="badge bg-dark-lt">رؤية طويلة</span></div>
        <div class="col-7"><div class="text-truncate">التحويل إلى منتج تجاري مع نظام تراخيص</div></div>
      </div>
    </div>
  </div>
</div>

<!-- 4. بطاقة المطور -->
<div class="card mt-4">
    <div class="card-body text-center">
        <div class="mb-3">
            <span class="avatar avatar-xl rounded" style="background-image: url(https://www.svgrepo.com/show/382100/developer-development-programming-software.svg)"></span>
        </div>
        <div class="card-title mb-1">ناجي قاسم</div>
        <div class="text-muted">مطور ومؤسس النظام</div>
    </div>
</div>