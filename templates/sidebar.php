<?php
// تحديد الصفحة الحالية لتفعيل الرابط
$current_page = $_GET['page'] ?? 'dashboard';
?>
<aside class="navbar navbar-expand-md d-print-none">
  <div class="container-xl">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
      <a href="index.php?page=dashboard">
        <!-- يمكنك وضع شعارك هنا -->
        نظام إدارة الأملاك
      </a>
    </h1>

    <div class="collapse navbar-collapse" id="navbar-menu">
      <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
        <ul class="navbar-nav">
          
          <li class="nav-item <?= ($current_page === 'dashboard') ? 'active' : '' ?>">
            <a class="nav-link" href="index.php?page=dashboard">
              <span class="nav-link-icon"><i class="ti ti-home"></i></span>
              <span class="nav-link-title">الرئيسية</span>
            </a>
          </li>

          <!-- ==================== قسم الإدارة الأساسية ==================== -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" role="button" aria-expanded="false">
              <span class="nav-link-icon"><i class="ti ti-building-community"></i></span>
              <span class="nav-link-title">الإدارة الأساسية</span>
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="index.php?page=branches">الفروع</a>
                <a class="dropdown-item" href="index.php?page=properties">العقارات</a>
                <a class="dropdown-item" href="index.php?page=units">الوحدات</a>
                <a class="dropdown-item" href="index.php?page=clients">العملاء</a>
                <a class="dropdown-item" href="index.php?page=suppliers">الموردين</a>
            </div>
          </li>

          <!-- ==================== قسم العقود والمالية ==================== -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#navbar-contracts" data-bs-toggle="dropdown" role="button" aria-expanded="false">
              <span class="nav-link-icon"><i class="ti ti-file-invoice"></i></span>
              <span class="nav-link-title">العقود والمالية</span>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="index.php?page=contracts">عقود الإيجار</a>
              <a class="dropdown-item" href="index.php?page=supply_contracts">عقود التوريد</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="index.php?page=financial">العمليات المالية</a>
            </div>
          </li>

          <!-- ==================== قسم الإدارة المتقدمة ==================== -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#navbar-admin" data-bs-toggle="dropdown" role="button" aria-expanded="false">
              <span class="nav-link-icon"><i class="ti ti-settings"></i></span>
              <span class="nav-link-title">إدارة النظام</span>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="index.php?page=users">المستخدمين</a>
              <a class="dropdown-item" href="index.php?page=roles">الأدوار</a>
              <a class="dropdown-item" href="index.php?page=permissions">الصلاحيات</a>
              <a class="dropdown-item" href="index.php?page=settings/lookups">تهيئة المدخلات</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="index.php?page=archive">الأرشيف</a>
              <a class="dropdown-item" href="index.php?page=about">حول النظام</a>
            </div>
          </li>

        </ul>
      </div>
    </div>
  </div>
</aside>