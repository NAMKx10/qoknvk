<?php
// هذا الملف: صفحة "حول النظام" برؤية إبداعية
$system_name = 'نظام إدارة الأملاك والأصول المتكامل';
$system_slogan = 'ذكاء إداري، قوة بيانات، مستقبل أعمالك.';
?>

<!-- 1. الهيدر والشعار -->
<div class="card card-md mb-4 shadow-sm">
    <div class="card-body text-center py-5">
        <img src="./assets/static/logo-white.svg" width="120" alt="Logo" class="mb-3">
        <h1 class="card-title mb-2" style="font-weight: bold; font-size: 2.2rem;">
            <?= htmlspecialchars($system_name) ?>
        </h1>
        <div class="text-primary mb-2" style="font-size: 1.1rem;">
            <i class="ti ti-rocket"></i> <?= $system_slogan ?>
        </div>
        <p class="text-muted" style="font-size:1.1rem;">
            منصة متكاملة لإدارة الأصول والعقارات، تجمع بين الذكاء البرمجي والتجربة العصرية، مصممة لتحويل البيانات إلى قرارات استراتيجية ناجحة.
        </p>
    </div>
</div>

<!-- 2. لماذا نحن مختلفون؟ -->
<div class="row row-cards mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-blue-lt">
            <div class="card-body text-center">
                <i class="ti ti-brain text-blue" style="font-size:2rem"></i>
                <div class="fw-bold mt-2">ذكاء وتحليل</div>
                <div class="text-muted small">تقارير فورية، تنبيهات ذكية، ودعم اتخاذ القرار.</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-green-lt">
            <div class="card-body text-center">
                <i class="ti ti-shield-lock text-green" style="font-size:2rem"></i>
                <div class="fw-bold mt-2">أمان عالي</div>
                <div class="text-muted small">نظام صلاحيات متقدم، وعزل بيانات شامل لكل عميل.</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-purple-lt">
            <div class="card-body text-center">
                <i class="ti ti-puzzle text-purple" style="font-size:2rem"></i>
                <div class="fw-bold mt-2">مرونة كاملة</div>
                <div class="text-muted small">تهيئة كل شيء من لوحة التحكم، بدون برمجة.</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-orange-lt">
            <div class="card-body text-center">
                <i class="ti ti-rocket text-orange" style="font-size:2rem"></i>
                <div class="fw-bold mt-2">جاهزية للتوسع</div>
                <div class="text-muted small">بنية تدعم النمو والتحول لمنصة SaaS تجارية.</div>
            </div>
        </div>
    </div>
</div>

<!-- 3. الرؤية والرسالة - بكروت أيقونية -->
<div class="row row-cards mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center">
                <span class="avatar avatar-lg bg-blue-lt me-3"><i class="ti ti-bulb"></i></span>
                <div>
                    <h4 class="mb-1">رؤيتنا</h4>
                    <div class="text-muted">
                        أن نكون المنصة الأولى لإدارة الأصول والأعمال في العالم العربي، من خلال الذكاء، المرونة، والأمان.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center">
                <span class="avatar avatar-lg bg-green-lt me-3"><i class="ti ti-target-arrow"></i></span>
                <div>
                    <h4 class="mb-1">رسالتنا</h4>
                    <div class="text-muted">
                        تمكين أصحاب القرار بأدوات عصرية تمنحهم رؤية شاملة وتحكمًا كاملاً في أصولهم.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. تايملاين خارطة الطريق -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title"><i class="ti ti-road me-2 text-purple"></i>خارطة الطريق والتطور</h3>
    </div>
    <div class="card-body">
        <ul class="timeline">
            <li class="timeline-item">
                <span class="timeline-point bg-green"></span>
                <div class="timeline-event">
                    <div class="text-green">✓ مكتمل</div>
                    <div class="fw-bold">تأسيس البنية التحتية والموديلات الأساسية</div>
                    <div class="small text-muted">قاعدة بيانات متقدمة، صلاحيات، فروع، وحدات.</div>
                </div>
            </li>
            <li class="timeline-item">
                <span class="timeline-point bg-yellow"></span>
                <div class="timeline-event">
                    <div class="text-yellow">قيد التنفيذ</div>
                    <div class="fw-bold">تطوير الواجهات باستخدام Tabler</div>
                    <div class="small text-muted">توحيد التصميم وتجربة المستخدم.</div>
                </div>
            </li>
            <li class="timeline-item">
                <span class="timeline-point bg-blue"></span>
                <div class="timeline-event">
                    <div class="text-blue">مستقبلي</div>
                    <div class="fw-bold">المركز المالي المتقدم (فواتير، شيكات)</div>
                </div>
            </li>
            <li class="timeline-item">
                <span class="timeline-point bg-purple"></span>
                <div class="timeline-event">
                    <div class="text-purple">مستقبلي</div>
                    <div class="fw-bold">نظام الملكية المتعددة والترقيم المستقل</div>
                </div>
            </li>
            <li class="timeline-item">
                <span class="timeline-point bg-dark"></span>
                <div class="timeline-event">
                    <div class="text-dark">رؤية طويلة</div>
                    <div class="fw-bold">التحول إلى منتج تجاري مع نظام تراخيص SaaS</div>
                </div>
            </li>
        </ul>
    </div>
</div>

<!-- 5. بطاقة المطوّر -->
<div class="card mt-4">
    <div class="card-body text-center">
        <div class="mb-3">
            <span class="avatar avatar-xl rounded" style="background-image: url(https://www.svgrepo.com/show/382100/developer-development-programming-software.svg)"></span>
        </div>
        <div class="card-title mb-1" style="font-size: 1.2rem; font-weight: bold;">ناجي قاسم</div>
        <div class="text-muted mb-2">مبرمج النظام وقائد التطوير</div>
        <div>
            <a href="mailto:admin@example.com" class="btn btn-outline-primary btn-sm mx-1"><i class="ti ti-mail"></i> تواصل</a>
            <a href="#" class="btn btn-outline-dark btn-sm mx-1"><i class="ti ti-brand-github"></i> GitHub</a>
        </div>
    </div>
</div>

<!-- 6. لمسة ختامية -->
<div class="alert alert-info mt-4 text-center" style="font-size:1rem">
    <i class="ti ti-lightbulb"></i>
    تطوير مستمر... كل فكرة جديدة منكم تصنع مستقبل النظام!
</div>

<style>
/* تايملاين Tabler مخصص */
.timeline {
    list-style: none;
    padding: 0;
    margin: 0;
    position: relative;
}
.timeline:before {
    content: '';
    position: absolute;
    right: 16px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #eee;
}
.timeline-item {
    position: relative;
    margin-bottom: 2rem;
    padding-right: 48px;
}
.timeline-point {
    position: absolute;
    right: 10px;
    top: 6px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    z-index: 1;
}
.timeline-event {
    background: #fff;
    border-radius: 7px;
    padding: 8px 16px;
    box-shadow: 0 1px 6px 0 rgba(60,72,88,.08);
}
@media (max-width: 600px) {
    .timeline-item { padding-right: 32px; }
    .timeline:before { right: 6px; }
    .timeline-point { right: 0; }
}
</style>