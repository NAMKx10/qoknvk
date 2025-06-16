<header class="navbar navbar-expand-md navbar-light d-print-none">
  <div class="container-xl">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
      <a href="index.php?page=dashboard">
          نظام إدارة الأملاك
      </a>
    </h1>
    <div class="navbar-nav flex-row order-md-last">
      <!-- مساحة الأيقونات المستقبلية -->
    </div>
  </div>
</header>
<div class="navbar-expand-md">
  <div class="collapse navbar-collapse" id="navbar-menu">
    <div class="navbar navbar-light">
      <div class="container-xl">
        <!-- هنا يتم وضع كود القائمة من ملف navbar.php الذي أرسلته -->
        <?php
            $current_page = $_GET['page'] ?? 'dashboard';
            $base_pages = ['branches', 'properties', 'units', 'clients', 'suppliers'];
            $contract_pages = ['contracts', 'supply_contracts', 'financial'];
            $admin_pages = ['users', 'roles', 'permissions', 'settings/lookups', 'archive', 'about'];
            $is_base_active = in_array($current_page, $base_pages);
            $is_contract_active = in_array($current_page, $contract_pages);
            $is_admin_active = in_array($current_page, $admin_pages);
        ?>
        <ul class="navbar-nav">
          <li class="nav-item <?= ($current_page === 'dashboard') ? 'active' : '' ?>">
            <a class="nav-link" href="index.php?page=dashboard"><span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-home"></i></span><span class="nav-link-title">الرئيسية</span></a>
          </li>
          <li class="nav-item dropdown <?= ($is_base_active) ? 'active' : '' ?>">
            <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" role="button" aria-expanded="<?= ($is_base_active) ? 'true' : 'false' ?>"><span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-building-community"></i></span><span class="nav-link-title">الإدارة الأساسية</span></a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="index.php?page=branches"><i class="ti ti-sitemap me-2"></i>الفروع</a>
              <a class="dropdown-item" href="index.php?page=properties"><i class="ti ti-building-arch me-2"></i>العقارات</a>
              <a class="dropdown-item" href="index.php?page=units"><i class="ti ti-door me-2"></i>الوحدات</a>
              <a class="dropdown-item" href="index.php?page=clients"><i class="ti ti-users me-2"></i>العملاء</a>
              <a class="dropdown-item" href="index.php?page=suppliers"><i class="ti ti-truck-delivery me-2"></i>الموردين</a>
            </div>
          </li>
          <li class="nav-item dropdown <?= ($is_contract_active) ? 'active' : '' ?>">
            <a class="nav-link dropdown-toggle" href="#navbar-contracts" data-bs-toggle="dropdown" role="button" aria-expanded="<?= ($is_contract_active) ? 'true' : 'false' ?>"><span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-file-invoice"></i></span><span class="nav-link-title">العقود والمالية</span></a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="index.php?page=contracts"><i class="ti ti-file-text me-2"></i>عقود الإيجار</a>
              <a class="dropdown-item" href="index.php?page=supply_contracts"><i class="ti ti-file-certificate me-2"></i>عقود التوريد</a>
            </div>
          </li>
          <li class="nav-item dropdown <?= ($is_admin_active) ? 'active' : '' ?>">
            <a class="nav-link dropdown-toggle" href="#navbar-admin" data-bs-toggle="dropdown" role="button" aria-expanded="<?= ($is_admin_active) ? 'true' : 'false' ?>"><span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-settings"></i></span><span class="nav-link-title">إدارة النظام</span></a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="index.php?page=users"><i class="ti ti-user-cog me-2"></i>المستخدمين</a>
              <a class="dropdown-item" href="index.php?page=roles"><i class="ti ti-user-shield me-2"></i>الأدوار</a>
              <a class="dropdown-item" href="index.php?page=permissions"><i class="ti ti-key me-2"></i>الصلاحيات</a>
              <a class="dropdown-item" href="index.php?page=settings/lookups"><i class="ti ti-tool me-2"></i>تهيئة المدخلات</a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="index.php?page=archive"><i class="ti ti-archive me-2"></i>الأرشيف</a>
              <a class="dropdown-item" href="index.php?page=about"><i class="ti ti-info-circle me-2"></i>حول النظام</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
